<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Campaign_Filter;
use App\Models\Campaign_Package;
use App\Models\Filter;
use App\Models\Kategori;
use App\Models\Penggalang_Dana;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'thumbnail' => 'required|image|mimes:jpg,jpeg,png|max:2048',
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
            // PACKAGE TIDAK WAJIB - HAPUS VALIDASI REQUIRED
            'packages' => 'nullable|array',
            'packages.*.title' => 'nullable|string|max:255',
            'packages.*.description' => 'nullable|string',
            'packages.*.nominal' => 'nullable|numeric|min:0', // DIUBAH: nullable, min:0
            'packages.*.image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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

            // Simpan Campaign
            $campaign = Campaign::create([
                'thumbnail' => $thumbnail,
                'judul' => $request->judul_campaign,
                'slug' => Str::slug($request->judul_campaign) . '-' . time(),
                'deskripsi' => RichText::clean($request->deskripsi_campaign),
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

            /*
            |--------------------------------------------------------------------------
            | Simpan Filter
            |--------------------------------------------------------------------------
            */

            foreach ($request->filter as $filter) {
                Campaign_Filter::create([
                    'campaign_id' => $campaign->id,
                    'filter_id' => $filter,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Simpan Package - HANYA JIKA ADA DAN NOMINAL > 0
            |--------------------------------------------------------------------------
            */

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
    /**
     * Menampilkan campaign – cari berdasarkan slug ATAU custom_slug
     */
    public function show($slug)
    {
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
            'campaignUpdates.campaign_update_gambar',
            'campaignFundraisers.user',
            'fundraisers'
        ])
            ->where('is_active', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('custom_slug', $slug);
            })
            ->firstOrFail();

        // Hitung total terkumpul dari donasi yang sudah settlement
        $totalTerkumpul = $campaign->donasi->sum('nominal');
        $totalDonatur = $campaign->donasi->count();

        // Tambahkan ke object campaign
        $campaign->terkumpul = $totalTerkumpul;
        $campaign->donasi_count = $totalDonatur;

        $updatesData = $campaign->campaignUpdates->map(function ($update) {
            return [
                'id' => $update->id,
                'judul' => $update->judul_update,
                'isi' => $update->isi_update,
                'tanggal' => $update->created_at->translatedFormat('d F Y'),
                'gambar' => $update->campaign_update_gambar->map(function ($gambar) {
                    return asset('storage/' . $gambar->gambar_update);
                })->values(),
            ];
        });

        return view('pages.campaign.show', compact('campaign', 'updatesData'));
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
        // Clean money inputs
        $request->merge([
            'target_donasi' => $this->cleanMoney($request->target_donasi),
            'minimal_donasi' => $this->cleanMoney($request->minimal_donasi),
        ]);

        if ($request->has('packages')) {
            $packages = $request->packages;
            foreach ($packages as $key => $package) {
                if (isset($package['nominal'])) {
                    $packages[$key]['nominal'] = $this->cleanMoney($package['nominal']);
                }
            }
            $request->merge(['packages' => $packages]);
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'nullable|date|after:tanggal_mulai',
            'target_donasi' => 'required|numeric|min:0',
            'minimal_donasi' => 'nullable|numeric|min:0',
            'kategori_id' => 'required|exists:kategori,id',
            'campaign_type' => 'required|in:regular,emergency,sustainable',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'filter' => 'array|max:4',
            'filter.*' => 'exists:filter,id',
            // PACKAGE TIDAK WAJIB
            'packages' => 'nullable|array',
            'packages.*.title' => 'nullable|string|max:255',
            'packages.*.description' => 'nullable|string',
            'packages.*.nominal' => 'nullable|numeric|min:0', // DIUBAH: nullable
            'packages.*.image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'custom_slug' => 'nullable|alpha_dash|unique:campaign,custom_slug,' . $campaign->id,
        ]);

        DB::beginTransaction();

        try {
            // Reset approval if type changed to emergency/sustainable
            $approvalStatus = $campaign->approval_status;

            $campaignType = $request->tanggal_berakhir ? $request->campaign_type : 'sustainable';

            if (!$request->tanggal_berakhir || (in_array($campaignType, ['emergency', 'sustainable']) && $campaign->campaign_type != $campaignType)) {
                $approvalStatus = 'pending';
            } elseif (!in_array($campaignType, ['emergency', 'sustainable'])) {
                $approvalStatus = null;
            }

            // Set minimal donasi ke 5000 jika tidak diisi
            $minimalDonasi = $request->minimal_donasi ?: 5000;

            // Update data
            $data = [
                'judul' => $validated['judul'],
                'deskripsi' => RichText::clean($validated['deskripsi']),
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
                'custom_slug' => $request->custom_slug ? Str::slug($request->custom_slug) : null,
            ];

            // Update slug if title changed
            if ($campaign->judul != $request->judul) {
                $data['slug'] = Str::slug($request->judul) . '-' . time();
            }

            // Handle thumbnail
            if ($request->hasFile('thumbnail')) {
                if ($campaign->thumbnail && file_exists(storage_path('app/public/' . $campaign->thumbnail))) {
                    unlink(storage_path('app/public/' . $campaign->thumbnail));
                }
                $thumbnail = $request->file('thumbnail')->store('campaign/thumbnail', 'public');
                $data['thumbnail'] = $thumbnail;
            }

            $campaign->update($data);

            // Update Filter
            Campaign_Filter::where('campaign_id', $campaign->id)->delete();

            if ($request->has('filter')) {
                foreach ($request->filter as $filter) {
                    Campaign_Filter::create([
                        'campaign_id' => $campaign->id,
                        'filter_id' => $filter,
                    ]);
                }
            }

            // Update Package
            if ($request->has('packages')) {
                $existingPackageIds = $campaign->packages->pluck('id')->toArray();
                $updatedPackageIds = [];

                foreach ($request->packages as $packageData) {
                    // SKIP jika nominal kosong atau 0
                    if (!isset($packageData['nominal']) || $packageData['nominal'] <= 0) {
                        continue;
                    }

                    if (isset($packageData['id']) && in_array($packageData['id'], $existingPackageIds)) {
                        // Update existing package
                        $package = Campaign_Package::find($packageData['id']);
                        if ($package) {
                            $updateData = [
                                'judul' => $packageData['title'] ?? 'Package',
                                'deskripsi' => $packageData['description'] ?? null,
                                'nominal' => $packageData['nominal'],
                            ];

                            if (isset($packageData['image']) && $packageData['image'] instanceof \Illuminate\Http\UploadedFile) {
                                if ($package->gambar && file_exists(storage_path('app/public/' . $package->gambar))) {
                                    unlink(storage_path('app/public/' . $package->gambar));
                                }
                                $gambar = $packageData['image']->store('campaign/package', 'public');
                                $updateData['gambar'] = $gambar;
                            }

                            $package->update($updateData);
                            $updatedPackageIds[] = $package->id;
                        }
                    } else {
                        // Create new package
                        $gambar = null;
                        if (isset($packageData['image']) && $packageData['image'] instanceof \Illuminate\Http\UploadedFile) {
                            $gambar = $packageData['image']->store('campaign/package', 'public');
                        }

                        $package = Campaign_Package::create([
                            'campaign_id' => $campaign->id,
                            'judul' => $packageData['title'] ?? 'Package',
                            'deskripsi' => $packageData['description'] ?? null,
                            'nominal' => $packageData['nominal'],
                            'gambar' => $gambar,
                        ]);
                        $updatedPackageIds[] = $package->id;
                    }
                }

                // Delete packages not in updated list
                $packagesToDelete = array_diff($existingPackageIds, $updatedPackageIds);
                if (!empty($packagesToDelete)) {
                    $packages = Campaign_Package::whereIn('id', $packagesToDelete)->get();
                    foreach ($packages as $package) {
                        if ($package->gambar && file_exists(storage_path('app/public/' . $package->gambar))) {
                            unlink(storage_path('app/public/' . $package->gambar));
                        }
                        $package->delete();
                    }
                }
            } else {
                // Jika tidak ada packages sama sekali, hapus semua packages yang ada
                $existingPackages = $campaign->packages;
                foreach ($existingPackages as $package) {
                    if ($package->gambar && file_exists(storage_path('app/public/' . $package->gambar))) {
                        unlink(storage_path('app/public/' . $package->gambar));
                    }
                    $package->delete();
                }
            }

            DB::commit();

            $message = $campaign->approval_status === 'pending'
                ? 'Campaign berhasil diupdate dan menunggu persetujuan admin'
                : 'Campaign berhasil diupdate';

            $redirectSlug = $campaign->custom_slug ?? $campaign->slug;
            return redirect()->route('campaign.show', $redirectSlug)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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