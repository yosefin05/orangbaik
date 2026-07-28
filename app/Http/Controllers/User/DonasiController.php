<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    /**
     * Menampilkan halaman create campaign
     */
    public function create()
    {
        $kategori = Kategori::all();

        return view('pages.campaign.create', compact('kategori'));
    }

    /**
     * Menyimpan campaign baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_campaign'      => 'required|string|max:255',
            'deskripsi_campaign'  => 'required|string',
            'tanggal_mulai'       => 'required|date',
            'tanggal_akhir'       => 'required|date|after_or_equal:tanggal_mulai',
            'target_donasi'       => 'required',
            'minimal_donasi'      => 'required',
            'kategori_campaign'   => 'required',
            'thumbnail'           => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Upload Thumbnail
        |--------------------------------------------------------------------------
        */

        $thumbnail = null;

        if ($request->hasFile('thumbnail')) {

            $thumbnail = $request
                ->file('thumbnail')
                ->store('campaign/thumbnail', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Hilangkan titik pada format rupiah
        |--------------------------------------------------------------------------
        */

        $target = preg_replace('/[^0-9]/', '', $request->target_donasi);
        $minimal = preg_replace('/[^0-9]/', '', $request->minimal_donasi);

        /*
        |--------------------------------------------------------------------------
        | Simpan Campaign
        |--------------------------------------------------------------------------
        */

        Campaign::create([

            'judul_campaign'      => $validated['judul_campaign'],
            'slug'                => Str::slug($validated['judul_campaign']) . '-' . time(),
            'deskripsi_campaign'  => $validated['deskripsi_campaign'],
            'thumbnail'           => $thumbnail,
            'tanggal_mulai'       => $validated['tanggal_mulai'],
            'tanggal_akhir'       => $validated['tanggal_akhir'],
            'target_donasi'       => $target,
            'minimal_donasi'      => $minimal,
            'kategori_id'         => $validated['kategori_campaign'],
            'user_id'             => Auth::id(),
            'status'              => 'pending',
        ]);

        return redirect()
            ->back()
            ->with('success', 'Campaign berhasil dibuat.');
    }
}