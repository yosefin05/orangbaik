<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Campaign_Filter;
use App\Models\Campaign_Package;
use App\Models\Filter;
use App\Models\Kategori;
use App\Models\Donasi;
use App\Models\Penggalang_Dana;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CampaignController extends Controller
{

    public function index(Request $request)
    {
        $now = Carbon::now();
        $filters = Filter::all();
        $kategori = Kategori::all();
        $penggalangDana = Penggalang_Dana::where('status', 'verified')->get();

        // tampilkan campaign aktif
        $query = Campaign::with(['penggalangDana', 'donasi.pembayaran', 'kategori', 'filter'])
            ->where('is_active', true)
            ->where('tanggal_mulai', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('tanggal_berakhir')
                    ->orWhere('tanggal_berakhir', '>=', $now);
            });

        // Hanya tampilkan campaign yang approved atau regular
        $query->where(function ($q) {
            $q->where('campaign_type', 'regular')
                ->orWhere('approval_status', 'approved')
                ->orWhereNull('approval_status');
        });

        // FILTER: Jenis Penggalang
        if ($request->filled('jenis_penggalang')) {
            $jenis = $request->jenis_penggalang;
            $query->whereHas('penggalangDana', function ($q) use ($jenis) {
                $q->where('jenis_penggalang', $jenis);
            });
        }

        // FILTER: Filter (checkbox multiple) 
        if ($request->filled('filter_ids')) {
            $filterIds = (array) $request->filter_ids;
            $query->whereHas('filter', function ($q) use ($filterIds) {
                $q->whereIn('filter.id', $filterIds);
            });
        }

        // FILTER: Kategori 
        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        // DARURAT (emergency + approved + aktif)
        $darurat = (clone $query)
            ->where('campaign_type', 'emergency')
            ->where('approval_status', 'approved')
            ->latest()
            ->take(8)
            ->get();

        // PEMBERDAYAAN (sustainable + approved + aktif)
        $pemberdayaan = (clone $query)
            ->where('campaign_type', 'sustainable')
            ->where('approval_status', 'approved')
            ->latest()
            ->take(8)
            ->get();

        // CAMPAIGN TERBARU (2 item untuk grid kecil)
        $campaignTerbaru = (clone $query)
            ->latest()
            ->take(2)
            ->get();

        $campaigns = (clone $query)
            ->where('campaign_type', 'regular')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // DATA UNTUK FILTER 
        $selectedJenis = $request->jenis_penggalang ?? '';
        $selectedFilterIds = $request->filter_ids ?? [];

        return view('pages.donasi', compact(
            'filters',
            'kategori',
            'darurat',
            'pemberdayaan',
            'campaignTerbaru',
            'campaigns',
            'selectedJenis',
            'selectedFilterIds',
            'penggalangDana'
        ));
    }

    public function create()
    {
        $kategori = Kategori::all();
        $filter = Filter::all();
        $today = date('Y-m-d');

        return view('pages.campaign.create', compact('kategori', 'filter', 'today'));
    }

    /**
     * Menyimpan campaign
     */
    public function store(Request $request)
    {
        // Clean money inputs before validation
        $request->merge([
            'target_donasi' => $this->cleanMoney($request->target_donasi),
            'minimal_donasi' => $this->cleanMoney($request->minimal_donasi),
        ]);

        // Clean packages nominal
        if ($request->has('packages')) {
            $packages = $request->packages;
            foreach ($packages as $key => $package) {
                if (isset($package['nominal'])) {
                    $packages[$key]['nominal'] = $this->cleanMoney($package['nominal']);
                }
            }
            $request->merge(['packages' => $packages]);
        }

        $request->validate([
            'thumbnail' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'judul_campaign' => 'required|string|max:255',
            'deskripsi_campaign' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'target_donasi' => 'required|numeric|min:1',
            'minimal_donasi' => 'nullable|numeric|min:0',
            'kategori_id' => 'required|exists:kategori,id',
            'campaign_type' => 'required|in:regular,emergency,sustainable',
            'filter' => 'required|array|min:1|max:4',
            'filter.*' => 'exists:filter,id',
            'packages' => 'nullable|array',
            'packages.*.title' => 'nullable|string|max:255',
            'packages.*.description' => 'nullable|string',
            'packages.*.nominal' => 'nullable|numeric|min:0',
            'packages.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'custom_slug' => 'nullable|alpha_dash|unique:campaign,custom_slug',
        ]);

        DB::beginTransaction();

        try {
            // Upload thumbnail
            $thumbnail = $request->file('thumbnail')->store('campaign/thumbnail', 'public');

            // Penggalang Dana
            $penggalang = Penggalang_Dana::where('user_id', Auth::id())->firstOrFail();

            // Campaign tanpa tanggal akhir diperlakukan sebagai sustainable.
            $campaignType = $request->tanggal_akhir ? $request->campaign_type : 'sustainable';
            $approvalStatus = in_array($campaignType, ['emergency', 'sustainable']) ? 'pending' : 'approved';

            // Siapkan custom_slug
            $customSlug = $request->custom_slug ? Str::slug($request->custom_slug) : null;

            // Set minimal donasi ke 5000 jika tidak diisi
            $minimalDonasi = $request->minimal_donasi ?: 5000;


            $deskripsiCampaign = $this->processImages($request->deskripsi_campaign);
            // Simpan Campaign
            $campaign = Campaign::create([
                'thumbnail' => $thumbnail,
                'judul' => $request->judul_campaign,
                'slug' => Str::slug($request->judul_campaign) . '-' . time(),
                'deskripsi' => RichText::clean($deskripsiCampaign),
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_berakhir' => $request->tanggal_akhir,
                'target_donasi' => $request->target_donasi,
                'minimal_donasi' => $minimalDonasi,
                'kategori_id' => $request->kategori_id,
                'campaign_type' => $campaignType,
                'approval_status' => $approvalStatus,
                'penggalang_dana_id' => $penggalang->id,
                'is_active' => true,
                'enable_quantity' => $request->boolean('enable_quantity'),
                'enable_nama_donatur' => $request->boolean('enable_donatur_name'),
                'enable_custom_nominal' => $request->boolean('enable_custom_nominal'),
                'custom_slug' => $customSlug,
            ]);

            // Simpan Filter
            foreach ($request->filter as $filter) {
                Campaign_Filter::create([
                    'campaign_id' => $campaign->id,
                    'filter_id' => $filter,
                ]);
            }

            // Simpan Package - HANYA JIKA ADA DAN NOMINAL > 0
            if ($request->has('packages') && is_array($request->packages)) {
                foreach ($request->packages as $package) {
                    // CEK: hanya simpan jika nominal ada dan > 0
                    if (isset($package['nominal']) && $package['nominal'] > 0) {
                        $gambar = null;

                        if (isset($package['image']) && $package['image'] instanceof \Illuminate\Http\UploadedFile) {
                            $gambar = $package['image']->store('campaign/package', 'public');
                        }

                        Campaign_Package::create([
                            'campaign_id' => $campaign->id,
                            'judul' => $package['title'] ?? 'Package',
                            'deskripsi' => $package['description'] ?? null,
                            'nominal' => $package['nominal'],
                            'gambar' => $gambar,
                        ]);
                    }
                }
            }

            DB::commit();

            $message = $campaign->approval_status === 'pending'
                ? 'Campaign berhasil dibuat dan menunggu persetujuan admin untuk tampil di section Darurat/Berkelanjutan'
                : 'Campaign berhasil dibuat.';

            $redirectSlug = $campaign->custom_slug ?? $campaign->slug;
            return redirect()
                ->route('campaign.show', $redirectSlug)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Clean money string to numeric
     */
    private function cleanMoney($value)
    {
        if (is_null($value)) {
            return null;
        }
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Menampilkan campaign – cari berdasarkan slug ATAU custom_slug
     */
    public function show($slug)
    {
        // ============================================================
        // 1. AMBIL DATA CAMPAIGN
        // ============================================================
        $campaign = Campaign::with([
            'penggalangDana',
            'donasi' => function ($query) {
                // HANYA donasi dengan pembayaran SETTLEMENT
                $query->whereHas('pembayaran', function ($q) {
                    $q->where('transaction_status', 'settlement');
                });
            },
            'donasi.user',
            'donasi.pembayaran',
            'campaignFundraisers.user',
            'campaignFundraisers.donasis.pembayaran',
            'fundraisers',
            'campaignUpdates',
            'kategori',
            'filter'
        ])
            ->where('is_active', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('custom_slug', $slug);
            })
            ->firstOrFail();

        // ============================================================
        // 2. HITUNG TOTAL TERKUMPUL & DONATUR
        // ============================================================
        $totalTerkumpul = $campaign->donasi->sum('nominal');
        $totalDonatur = $campaign->donasi->count();

        // Tambahkan ke object campaign
        $campaign->terkumpul = $totalTerkumpul;
        $campaign->donasi_count = $totalDonatur;

        // ============================================================
        // 3. AMBIL PESAN DOA (HANYA SETTLEMENT + ADA PESAN)
        // ============================================================
        $pesanDoa = Donasi::with(['user', 'pembayaran'])
            ->where('campaign_id', $campaign->id)
            ->whereHas('pembayaran', function ($q) {
                $q->where('transaction_status', 'settlement');
            })
            ->whereNotNull('pesan_doa')
            ->where('pesan_doa', '!=', '')
            ->latest()
            ->take(10)
            ->get();

        // ============================================================
        // 4. CAMPAIGN LAIN UNTUK SIDEBAR (TANPA YANG SEDANG DILIHAT)
        // ============================================================
        $campaignLain = Campaign::with(['penggalangDana'])
            ->where('is_active', true)
            ->where('id', '!=', $campaign->id)
            ->where('tanggal_mulai', '<=', now())
            ->where(function ($query) {
                $query->whereNull('tanggal_berakhir')
                    ->orWhere('tanggal_berakhir', '>=', now());
            })
            ->latest()
            ->take(5)
            ->get();

        // ============================================================
        // 5. HANDLE REFERRAL CODE (jika ada di URL)
        // ============================================================
        if (request()->filled('ref')) {
            $fundraiser = $campaign->fundraisers()
                ->where('referral_code', request('ref'))
                ->where('status', 'active')
                ->first();

            if ($fundraiser) {
                session()->put('campaign_referral.' . $campaign->id, $fundraiser->referral_code);
            }
        }

        // ============================================================
        // 6. CEK APAKAH USER ADALAH FUNDRAISER
        // ============================================================
        if (auth()->check()) {
            $isFundraiser = $campaign->fundraisers()
                ->where('user_id', auth()->id())
                ->where('status', 'active')
                ->exists();

            view()->share('isFundraiser', $isFundraiser);
        }

        // ============================================================
        // 7. RETURN VIEW DENGAN SEMUA DATA
        // ============================================================
        return view('pages.campaign.show', compact(
            'campaign',
            'totalTerkumpul',
            'totalDonatur',
            'pesanDoa',
            'campaignLain'
        ));
    }

    public function edit(Campaign $campaign)
    {
        if ($campaign->penggalang_dana_id !== auth()->user()->penggalangDana->id) {
            abort(403);
        }

        $kategori = Kategori::all();
        $filter = Filter::all();

        $campaign->load([
            'packages',
            'filter',
            'kategori'
        ]);

        return view(
            'pages.campaign.edit',
            compact(
                'campaign',
                'kategori',
                'filter'
            )
        );
    }

    public function update(Request $request, Campaign $campaign)
    {
        // ============================================================
        // CLEAN MONEY INPUTS
        // ============================================================
        $request->merge([
            'target_donasi' => $this->cleanMoney($request->target_donasi),
            'minimal_donasi' => $this->cleanMoney($request->minimal_donasi),
        ]);

        if ($request->has('packages') && is_array($request->packages)) {
            $packages = $request->packages;

            foreach ($packages as $key => $package) {
                if (isset($package['nominal'])) {
                    $packages[$key]['nominal'] = $this->cleanMoney($package['nominal']);
                }
            }

            $request->merge(['packages' => $packages]);
        }

        // ============================================================
        // VALIDATION
        // ============================================================
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => ['nullable', 'date', 'after:tanggal_mulai'],
            'target_donasi' => ['required', 'numeric', 'min:0'],
            'minimal_donasi' => ['nullable', 'numeric', 'min:0'],
            'kategori_id' => ['required', 'exists:kategori,id'],
            'campaign_type' => ['required', 'in:regular,emergency,sustainable'],

            // WEBP SUPPORT
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],

            'filter' => ['nullable', 'array', 'max:4'],
            'filter.*' => ['exists:filter,id'],

            'packages' => ['nullable', 'array'],
            'packages.*.id' => ['nullable', 'exists:campaign_packages,id'],
            'packages.*.title' => ['nullable', 'string', 'max:255'],
            'packages.*.description' => ['nullable', 'string'],
            'packages.*.nominal' => ['nullable', 'numeric', 'min:0'],

            // WEBP SUPPORT
            'packages.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],

            'custom_slug' => [
                'nullable',
                'alpha_dash',
                'unique:campaign,custom_slug,' . $campaign->id,
            ],
        ]);

        // ============================================================
        // TRANSACTION
        // ============================================================
        DB::beginTransaction();

        try {
            // ========================================================
            // SAVE ORIGINAL VALUES
            // ========================================================
            $oldTitle = $campaign->judul;
            $oldThumbnail = $campaign->thumbnail;

            // Simpan daftar package existing SEBELUM perubahan, agar
            // reference package historical tidak hilang.
            $existingPackages = $campaign->packages()->get();
            $existingPackageIds = $existingPackages->pluck('id')->toArray();
            $updatedPackageIds = [];

            // ========================================================
            // DETERMINE CAMPAIGN TYPE
            // ========================================================
            $campaignType = $request->tanggal_berakhir
                ? $request->campaign_type
                : 'sustainable';

            // ========================================================
            // APPROVAL STATUS
            // ========================================================
            $approvalStatus = $campaign->approval_status;

            if (
                !$request->tanggal_berakhir
                || (
                    in_array($campaignType, ['emergency', 'sustainable'], true)
                    && $campaign->campaign_type !== $campaignType
                )
            ) {
                $approvalStatus = 'pending';
            } elseif (!in_array($campaignType, ['emergency', 'sustainable'], true)) {
                $approvalStatus = null;
            }

            // ========================================================
            // MINIMAL DONATION
            // ========================================================
            $minimalDonasi = $request->minimal_donasi ?: 5000;

            // ========================================================
            // BUILD CAMPAIGN DATA
            // ========================================================
            $data = [
                'judul' => $validated['judul'],
                'deskripsi' => RichText::clean($this->processImages($validated['deskripsi'])),
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_berakhir' => $validated['tanggal_berakhir'],
                'target_donasi' => $validated['target_donasi'],
                'minimal_donasi' => $minimalDonasi,
                'kategori_id' => $validated['kategori_id'],
                'campaign_type' => $campaignType,
                'approval_status' => $approvalStatus,
                'enable_quantity' => $request->boolean('enable_quantity'),
                'enable_nama_donatur' => $request->boolean('enable_donatur_name'),
                'enable_custom_nominal' => $request->boolean('enable_custom_nominal'),
                'custom_slug' => !empty($request->custom_slug)
                    ? Str::slug($request->custom_slug)
                    : null,
            ];

            // ========================================================
            // UPDATE SYSTEM SLUG ONLY WHEN TITLE CHANGES
            // ========================================================
            if ($oldTitle !== $validated['judul']) {
                $data['slug'] = Str::slug($validated['judul']) . '-' . time();
            }

            // ========================================================
            // HANDLE CAMPAIGN THUMBNAIL
            // ========================================================
            if ($request->hasFile('thumbnail')) {
                $thumbnail = $request->file('thumbnail')->store('campaign/thumbnail', 'public');

                if ($oldThumbnail && file_exists(storage_path('app/public/' . $oldThumbnail))) {
                    unlink(storage_path('app/public/' . $oldThumbnail));
                }

                $data['thumbnail'] = $thumbnail;
            }

            // ========================================================
            // UPDATE CAMPAIGN
            // ========================================================
            $campaign->update($data);
            $campaign->refresh(); // refresh slug/custom_slug terbaru

            // ========================================================
            // UPDATE FILTER
            // ========================================================
            Campaign_Filter::where('campaign_id', $campaign->id)->delete();

            if ($request->has('filter') && is_array($request->filter)) {
                foreach ($request->filter as $filterId) {
                    Campaign_Filter::create([
                        'campaign_id' => $campaign->id,
                        'filter_id' => $filterId,
                    ]);
                }
            }

            // ========================================================
            // UPDATE PACKAGES
            // ========================================================
            if ($request->has('packages') && is_array($request->packages)) {
                foreach ($request->packages as $packageData) {
                    // Ignore invalid/empty package rows
                    if (
                        !isset($packageData['nominal'])
                        || (float) $packageData['nominal'] <= 0
                    ) {
                        continue;
                    }

                    $packageId = $packageData['id'] ?? null;

                    // ----------------------------------------------------
                    // PACKAGE IMAGE
                    // ----------------------------------------------------
                    $imagePath = null;

                    if (
                        isset($packageData['image'])
                        && $packageData['image'] instanceof \Illuminate\Http\UploadedFile
                    ) {
                        $imagePath = $packageData['image']->store('campaign/package', 'public');
                    }

                    // =================================================
                    // UPDATE EXISTING PACKAGE
                    // =================================================
                    if ($packageId && in_array($packageId, $existingPackageIds, true)) {
                        $package = Campaign_Package::find($packageId);

                        if (!$package) {
                            continue;
                        }

                        // Jangan ubah historical donation record.
                        // Package master boleh berubah jika belum digunakan.
                        $updateData = [
                            'judul' => $packageData['title'] ?? 'Package',
                            'deskripsi' => $packageData['description'] ?? null,
                            'nominal' => $packageData['nominal'],
                        ];

                        // Replace package image hanya ketika ada image baru
                        if ($imagePath) {
                            $oldPackageImage = $package->gambar;
                            $updateData['gambar'] = $imagePath;

                            $package->update($updateData);

                            if (
                                $oldPackageImage
                                && file_exists(storage_path('app/public/' . $oldPackageImage))
                            ) {
                                unlink(storage_path('app/public/' . $oldPackageImage));
                            }
                        } else {
                            $package->update($updateData);
                        }

                        $updatedPackageIds[] = $package->id;
                        continue;
                    }

                    // =================================================
                    // CREATE NEW PACKAGE
                    // =================================================
                    $package = Campaign_Package::create([
                        'campaign_id' => $campaign->id,
                        'judul' => $packageData['title'] ?? 'Package',
                        'deskripsi' => $packageData['description'] ?? null,
                        'nominal' => $packageData['nominal'],
                        'gambar' => $imagePath,
                    ]);

                    $updatedPackageIds[] = $package->id;
                }
            }

            // ========================================================
            // PACKAGE DELETION
            // ========================================================
            // JANGAN langsung hapus package yang pernah dipakai donation.
            // Hanya hapus package yang:
            // - tidak lagi dikirim dari form
            // - BELUM pernah dipakai transaksi
            // ========================================================
            $packagesToDelete = array_diff($existingPackageIds, $updatedPackageIds);

            if (!empty($packagesToDelete)) {
                $packages = Campaign_Package::whereIn('id', $packagesToDelete)->get();

                foreach ($packages as $package) {
                    // Cek apakah package pernah digunakan donation.
                    $hasHistoricalDonation = false;

                    if (\Schema::hasColumn('donasi', 'campaign_package_id')) {
                        $hasHistoricalDonation = DB::table('donasi')
                            ->where('campaign_package_id', $package->id)
                            ->exists();
                    } elseif (\Schema::hasColumn('donasi', 'package_id')) {
                        $hasHistoricalDonation = DB::table('donasi')
                            ->where('package_id', $package->id)
                            ->exists();
                    }

                    // ----------------------------------------------------
                    // HISTORICAL PACKAGE → jangan delete
                    // ----------------------------------------------------
                    if ($hasHistoricalDonation) {
                        if (\Schema::hasColumn('campaign_packages', 'is_active')) {
                            $package->update(['is_active' => false]);
                        }

                        continue;
                    }

                    // ----------------------------------------------------
                    // UNUSED PACKAGE → aman untuk dihapus
                    // ----------------------------------------------------
                    if (
                        $package->gambar
                        && file_exists(storage_path('app/public/' . $package->gambar))
                    ) {
                        unlink(storage_path('app/public/' . $package->gambar));
                    }

                    $package->delete();
                }
            }

            // ========================================================
            // COMMIT
            // ========================================================
            DB::commit();

            // ========================================================
            // REDIRECT
            // ========================================================
            $campaign->refresh();

            $redirectSlug = $campaign->custom_slug ?: $campaign->slug;

            $message = $campaign->approval_status === 'pending'
                ? 'Campaign berhasil diupdate dan menunggu persetujuan admin'
                : 'Campaign berhasil diupdate';

            return redirect()
                ->route('campaign.show', ['slug' => $redirectSlug])
                ->with('success', $message);

        } catch (\Throwable $e) {
            // ========================================================
            // ROLLBACK
            // ========================================================
            DB::rollBack();

            \Log::error('Campaign update failed', [
                'campaign_id' => $campaign->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui campaign. Silakan coba lagi.');
        }
    }

    private function processImages(string $html): string
    {
        return preg_replace_callback(
            '/<img([^>]+)src=["\']data:image\/(jpeg|jpg|png|webp);base64,([^"\']+)["\']([^>]*)>/i',
            function ($matches) {
                $attributesBefore = $matches[1];
                $extension = strtolower($matches[2]);
                $base64 = $matches[3];
                $attributesAfter = $matches[4];

                // Decode Base64
                $imageData = base64_decode($base64, true);

                // Kalau gagal decode, biarkan gambar seperti semula
                if ($imageData === false) {
                    return $matches[0];
                }

                // Pastikan ekstensi valid
                if ($extension === 'jpg') {
                    $extension = 'jpeg';
                }

                // Buat nama file unik
                $filename = Str::uuid() . '.' . $extension;

                // Simpan ke storage/app/public/campaign
                $path = 'campaign/gambar/' . $filename;

                Storage::disk('public')->put($path, $imageData);

                // URL yang akan disimpan di database
                $url = '/storage/' . ltrim($path, '/');
                return '<img'
                    . $attributesBefore
                    . 'src="' . $url . '"'
                    . $attributesAfter
                    . '>';
            },
            $html
        );
    }

    public function destroy(Campaign $campaign)
    {
        if ($campaign->penggalang_dana_id !== auth()->user()->penggalangDana->id) {
            abort(403);
        }

        $campaign->delete();

        return back()->with(
            'success',
            'Campaign berhasil dihapus.'
        );
    }
}