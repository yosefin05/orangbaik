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
        $kategori = Kategori::all();

        $query = Campaign::with([
            'kategori',
            'penggalangDana',
            'donasi'
        ])->where('is_active', true);

        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        $darurat = (clone $query)
            ->whereHas('kategori', function ($q) {
                $q->where('nama_kategori', 'Kemanusiaan');
            })
            ->latest()
            ->take(8)
            ->get();

        $terbaru = (clone $query)
            ->latest()
            ->take(8)
            ->get();

        $pemberdayaan = (clone $query)
            ->whereHas('kategori', function ($q) {
                $q->where('nama_kategori', 'Sedekah Rutin');
            })
            ->latest()
            ->take(8)
            ->get();

        $campaignTerbaru = Campaign::with('penggalangDana')
            ->where('is_active', true)
            ->latest()
            ->take(2)
            ->get();

        $campaigns = Campaign::with([
            'penggalangDana',
            'donasi'
        ])
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('pages.donasi', compact(
            'kategori',
            'campaigns',
            'darurat',
            'terbaru',
            'pemberdayaan',
            'campaignTerbaru'
        ));
    }

    public function create()
    {
        $kategori = Kategori::all();
        $filter = Filter::all();

        return view('pages.create-campaign', compact('kategori', 'filter'));
    }

    /**
     * Menyimpan campaign
     */
    public function store(Request $request)
    {
        $request->validate([
            'thumbnail' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'judul_campaign' => 'required|string|max:255',
            'deskripsi_campaign' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
            'target_donasi' => 'required',
            'minimal_donasi' => 'required',
            'kategori_id' => 'required|exists:kategori,id',
            'filter' => 'required|array|min:1|max:4',
            'filter.*' => 'exists:filter,id',
            'gambar_pendukung.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'packages' => 'required|array|min:1',
            'packages.*.title' => 'required|string|max:255',
            'packages.*.description' => 'nullable|string',
            'packages.*.nominal' => 'required',
            'packages.*.image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {

            // Upload thumbnail
            $thumbnail = $request->file('thumbnail')
                ->store('campaign/thumbnail', 'public');

            // Penggalang Dana
            $penggalang = Penggalang_Dana::where('user_id', Auth::id())->firstOrFail();

            // Simpan Campaign
            $campaign = Campaign::create([
                'thumbnail' => $thumbnail,
                'judul' => $request->judul_campaign,
                'slug' => Str::slug($request->judul_campaign) . '-' . time(),
                'deskripsi' => $request->deskripsi_campaign,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_berakhir' => $request->tanggal_akhir,
                'target_donasi' => str_replace('.', '', $request->target_donasi),
                'minimal_donasi' => str_replace('.', '', $request->minimal_donasi),
                'kategori_id' => $request->kategori_id,
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
                    'nominal' => str_replace('.', '', $package['nominal']),
                    'gambar' => $gambar,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('campaign.create')
                ->with('success', 'Campaign berhasil dibuat.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show($slug)
    {
        $campaign = Campaign::with([
            'kategori',
            'penggalangDana',
            'campaignGambar',
            'packages',
            'campaignFilter.filter'
        ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('pages.detail-campaign', compact('campaign'));
    }
}