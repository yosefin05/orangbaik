<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Campaign_Filter;
use App\Models\Campaign_Gambar;
use App\Models\Campaign_Package;
use App\Models\Filter;
use App\Models\Kategori;
use App\Models\Penggalang_Dana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CampaignController extends Controller
{

    public function index(Request $request)
    {
        // Ambil semua filter dari database (tabel: filter)
        $filters = Filter::all();

        // Ambil kategori untuk tombol cepat
        $kategori = Kategori::all();

        // Query dasar: campaign aktif
        $query = Campaign::with([
            'kategori',
            'penggalangDana',
            'donasi',
            'filter' // relasi many-to-many ke filter
        ])->where('is_active', true);

        // Hanya tampilkan campaign yang approved atau regular
        $query->where(function ($q) {
            $q->where('campaign_type', 'regular')
                ->orWhere('approval_status', 'approved')
                ->orWhereNull('approval_status');
        });

        // === FILTER: Jenis Penggalang ===
        if ($request->filled('jenis_penggalang')) {
            $jenis = $request->jenis_penggalang;
            $query->whereHas('penggalangDana', function ($q) use ($jenis) {
                $q->where('jenis_penggalang', $jenis);
            });
        }

        // === FILTER: Filter (checkbox multiple dari tabel `filter`) ===
        if ($request->filled('filter_ids')) {
            $filterIds = (array) $request->filter_ids;
            $query->whereHas('filter', function ($q) use ($filterIds) {
                $q->whereIn('filter.id', $filterIds);
            });
        }

        // === FILTER: Kategori (tombol cepat) ===
        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        // === AMBIL DATA UNTUK SECTION ===

        // Darurat - emergency approved
        $darurat = (clone $query)
            ->where('campaign_type', 'emergency')
            ->where('approval_status', 'approved')
            ->latest()
            ->take(8)
            ->get();

        // Terbaru - semua campaign
        $terbaru = (clone $query)
            ->where(function ($q) {
                $q->where('campaign_type', 'regular')
                    ->orWhere('approval_status', 'approved');
            })
            ->latest()
            ->take(8)
            ->get();

        // Pemberdayaan - sustainable approved
        $pemberdayaan = (clone $query)
            ->where('campaign_type', 'sustainable')
            ->where('approval_status', 'approved')
            ->latest()
            ->take(8)
            ->get();

        // Campaign terbaru (2 item untuk grid kecil)
        $campaignTerbaru = (clone $query)
            ->where(function ($q) {
                $q->where('campaign_type', 'regular')
                    ->orWhere('approval_status', 'approved');
            })
            ->latest()
            ->take(2)
            ->get();

        // Campaign dengan pencapaian < 30%
        $belumSampai30 = (clone $query)
            ->where(function ($q) {
                $q->where('campaign_type', 'regular')
                    ->orWhere('approval_status', 'approved');
            })
            ->get()
            ->filter(function ($campaign) {
                $terkumpul = $campaign->donasi->sum('nominal');
                $target = $campaign->target_donasi;
                if ($target == 0) return false;
                return ($terkumpul / $target) < 0.3;
            })
            ->take(8);

        // === DATA UNTUK FILTER ===
        $selectedJenis = $request->jenis_penggalang ?? '';
        $selectedFilterIds = $request->filter_ids ?? [];

        return view('pages.donasi', compact(
            'filters',        // <-- data filter dari database
            'kategori',
            'darurat',
            'terbaru',
            'pemberdayaan',
            'campaignTerbaru',
            'belumSampai30',
            'selectedJenis',
            'selectedFilterIds'
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
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
            'target_donasi' => 'required|numeric|min:1',
            'minimal_donasi' => 'required|numeric|min:1',
            'kategori_id' => 'required|exists:kategori,id',
            'campaign_type' => 'required|in:regular,emergency,sustainable',
            'filter' => 'required|array|min:1|max:4',
            'filter.*' => 'exists:filter,id',
            'gambar_pendukung.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'packages' => 'required|array|min:1',
            'packages.*.title' => 'nullable|string|max:255',
            'packages.*.description' => 'nullable|string',
            'packages.*.nominal' => 'required|numeric|min:1',
            'packages.*.image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // Upload thumbnail
            $thumbnail = $request->file('thumbnail')
                ->store('campaign/thumbnail', 'public');

            // Penggalang Dana
            $penggalang = Penggalang_Dana::where('user_id', Auth::id())->firstOrFail();

            // Set approval only for emergency and sustainable
            $approvalStatus = in_array($request->campaign_type, ['emergency', 'sustainable'])
                ? 'pending'
                : 'approved';

            // Simpan Campaign
            $campaign = Campaign::create([
                'thumbnail' => $thumbnail,
                'judul' => $request->judul_campaign,
                'slug' => Str::slug($request->judul_campaign) . '-' . time(),
                'deskripsi' => $request->deskripsi_campaign,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_berakhir' => $request->tanggal_akhir,
                'target_donasi' => $request->target_donasi,
                'minimal_donasi' => $request->minimal_donasi,
                'kategori_id' => $request->kategori_id,
                'campaign_type' => $request->campaign_type,
                'approval_status' => $approvalStatus,
                'penggalang_dana_id' => $penggalang->id,
                'is_active' => true,
                'enable_quantity' => $request->boolean('enable_quantity'),
                'enable_nama_donatur' => $request->boolean('enable_donatur_name'),
                'enable_custom_nominal' => $request->boolean('enable_custom_nominal'),
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
            | Simpan Gambar Pendukung
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('gambar_pendukung')) {
                foreach ($request->file('gambar_pendukung') as $gambar) {
                    $path = $gambar->store(
                        'campaign/gambar',
                        'public'
                    );

                    Campaign_Gambar::create([
                        'campaign_id' => $campaign->id,
                        'gambar' => $path,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Simpan Package
            |--------------------------------------------------------------------------
            */

            foreach ($request->packages as $package) {
                $gambar = null;

                if (
                    isset($package['image']) &&
                    $package['image'] instanceof \Illuminate\Http\UploadedFile
                ) {
                    $gambar = $package['image']->store(
                        'campaign/package',
                        'public'
                    );
                }

                Campaign_Package::create([
                    'campaign_id' => $campaign->id,
                    'judul' => $package['title'],
                    'deskripsi' => $package['description'] ?? null,
                    'nominal' => $package['nominal'],
                    'gambar' => $gambar,
                ]);
            }

            DB::commit();

            $message = $campaign->approval_status === 'pending'
                ? 'Campaign berhasil dibuat dan menunggu persetujuan admin untuk tampil di section Darurat/Berkelanjutan'
                : 'Campaign berhasil dibuat.';

            return redirect()
                ->route('campaign.show', $campaign->slug)
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
        // Remove dots, commas, spaces, and "Rp" prefix
        return preg_replace('/[^0-9]/', '', $value);
    }

    public function show($slug)
    {
        $campaign = Campaign::with([
            'penggalangDana',
            'donasi.user',
            'campaignUpdates.campaign_update_gambar',
            'campaignFundraisers.user'
        ])
        ->where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

        $campaign->terkumpul = $campaign->donasi->sum('nominal');
        $campaign->donasi_count = $campaign->donasi->count();

        // Siapkan data untuk modal
        $updatesData = $campaign->campaignUpdates->map(function($update) {
            return [
                'id' => $update->id,
                'judul' => $update->judul_update,
                'isi' => $update->isi_update,
                'tanggal' => $update->created_at->translatedFormat('d F Y'),
                'gambar' => $update->campaign_update_gambar->map(function($gambar) {
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
            'campaignGambar',
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

        // Validasi
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'required|date|after:tanggal_mulai',
            'target_donasi' => 'required|numeric|min:0',
            'minimal_donasi' => 'required|numeric|min:0',
            'kategori_id' => 'required|exists:kategori,id',
            'campaign_type' => 'required|in:regular,emergency,sustainable',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'filter' => 'array|max:4',
            'filter.*' => 'exists:filter,id',
            'gambar_pendukung.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'packages' => 'array|min:1',
            'packages.*.title' => 'nullable|string|max:255',
            'packages.*.description' => 'nullable|string',
            'packages.*.nominal' => 'required|numeric|min:1',
            'packages.*.image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // Reset approval if type changed to emergency/sustainable
            $approvalStatus = $campaign->approval_status;

            if (in_array($request->campaign_type, ['emergency', 'sustainable']) &&
                $campaign->campaign_type != $request->campaign_type) {
                $approvalStatus = 'pending';
            } elseif (!in_array($request->campaign_type, ['emergency', 'sustainable'])) {
                $approvalStatus = null;
            }

            // Update data
            $data = [
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_berakhir' => $validated['tanggal_berakhir'],
                'target_donasi' => $validated['target_donasi'],
                'minimal_donasi' => $validated['minimal_donasi'],
                'kategori_id' => $validated['kategori_id'],
                'campaign_type' => $request->campaign_type,
                'approval_status' => $approvalStatus,
                'enable_quantity' => $request->boolean('enable_quantity'),
                'enable_nama_donatur' => $request->boolean('enable_donatur_name'),
                'enable_custom_nominal' => $request->boolean('enable_custom_nominal'),
            ];

            // Update slug if title changed
            if ($campaign->judul != $request->judul) {
                $data['slug'] = Str::slug($request->judul) . '-' . time();
            }

            // Handle thumbnail
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail
                if ($campaign->thumbnail && file_exists(storage_path('app/public/' . $campaign->thumbnail))) {
                    unlink(storage_path('app/public/' . $campaign->thumbnail));
                }
                $thumbnail = $request->file('thumbnail')->store('campaign/thumbnail', 'public');
                $data['thumbnail'] = $thumbnail;
            }

            $campaign->update($data);

            /*
            |--------------------------------------------------------------------------
            | Update Filter
            |--------------------------------------------------------------------------
            */

            // Delete existing filters
            Campaign_Filter::where('campaign_id', $campaign->id)->delete();

            // Create new filters
            if ($request->has('filter')) {
                foreach ($request->filter as $filter) {
                    Campaign_Filter::create([
                        'campaign_id' => $campaign->id,
                        'filter_id' => $filter,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Update Gambar Pendukung
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('gambar_pendukung')) {
                // Delete existing images
                $existingImages = Campaign_Gambar::where('campaign_id', $campaign->id)->get();
                foreach ($existingImages as $img) {
                    if (file_exists(storage_path('app/public/' . $img->gambar))) {
                        unlink(storage_path('app/public/' . $img->gambar));
                    }
                    $img->delete();
                }

                // Upload new images
                foreach ($request->file('gambar_pendukung') as $gambar) {
                    if ($gambar) {
                        $path = $gambar->store('campaign/gambar', 'public');
                        Campaign_Gambar::create([
                            'campaign_id' => $campaign->id,
                            'gambar' => $path,
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Update Package
            |--------------------------------------------------------------------------
            */

            if ($request->has('packages')) {
                // Get existing package IDs
                $existingPackageIds = $campaign->packages->pluck('id')->toArray();
                $updatedPackageIds = [];

                foreach ($request->packages as $packageData) {
                    if (isset($packageData['id']) && in_array($packageData['id'], $existingPackageIds)) {
                        // Update existing package
                        $package = Campaign_Package::find($packageData['id']);
                        if ($package) {
                            $updateData = [
                                'judul' => $packageData['title'],
                                'deskripsi' => $packageData['description'] ?? null,
                                'nominal' => $packageData['nominal'],
                            ];

                            // Handle image update
                            if (
                                isset($packageData['image']) &&
                                $packageData['image'] instanceof \Illuminate\Http\UploadedFile
                            ) {
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
                        if (
                            isset($packageData['image']) &&
                            $packageData['image'] instanceof \Illuminate\Http\UploadedFile
                        ) {
                            $gambar = $packageData['image']->store('campaign/package', 'public');
                        }

                        $package = Campaign_Package::create([
                            'campaign_id' => $campaign->id,
                            'judul' => $packageData['title'],
                            'deskripsi' => $packageData['description'] ?? null,
                            'nominal' => $packageData['nominal'],
                            'gambar' => $gambar,
                        ]);
                        $updatedPackageIds[] = $package->id;
                    }
                }

                // Delete packages that are not in the updated list
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
            }

            DB::commit();

            $message = $campaign->approval_status === 'pending'
                ? 'Campaign berhasil diupdate dan menunggu persetujuan admin'
                : 'Campaign berhasil diupdate';

            return redirect()->route('campaign.show', $campaign->slug)
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