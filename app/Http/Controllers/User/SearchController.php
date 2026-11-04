<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Berita;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('q', ''));

        /*
        |--------------------------------------------------------------------------
        | Search Context
        |--------------------------------------------------------------------------
        | campaign = hanya campaign
        | berita   = hanya berita
        | default  = campaign (landing dan donasi)
        */

        $requestedType = $request->input('type');

        $type = in_array($requestedType, ['campaign', 'berita'], true)
            ? $requestedType
            : 'campaign';

        $campaigns = collect();
        $berita = collect();

        if ($keyword !== '') {

            /*
            |--------------------------------------------------------------------------
            | SEARCH CAMPAIGN
            |--------------------------------------------------------------------------
            */

            if ($type === 'campaign') {

                $settlementDonasi = function ($query) {
                    $query->whereHas('pembayaran', function ($paymentQuery) {
                        $paymentQuery->where(
                            'transaction_status',
                            'settlement'
                        );
                    });
                };

                $campaigns = Campaign::with([
                    'penggalangDana',
                    'kategori',
                ])
                    ->withSum([
                        'donasi as total_terkumpul' => $settlementDonasi,
                    ], 'nominal')
                    ->withCount([
                        'donasi as jumlah_donatur' => $settlementDonasi,
                    ])
                    ->where('is_active', true)
                    ->where(function ($query) {
                        $query->where('campaign_type', 'regular')
                            ->orWhere('approval_status', 'approved');
                    })

                    // Jangan tampilkan campaign yang sudah melewati tanggal berakhir.
                    ->where(function ($query) {
                        $query->whereNull('tanggal_berakhir')
                            ->orWhereDate(
                                'tanggal_berakhir',
                                '>=',
                                now()->toDateString()
                            );
                    })

                    ->where('judul', 'LIKE', '%' . $keyword . '%')
                    ->latest()
                    ->get();
            }

            /*
            |--------------------------------------------------------------------------
            | SEARCH BERITA
            |--------------------------------------------------------------------------
            */

            if ($type === 'berita') {

                $berita = Berita::where(
                    'judul',
                    'LIKE',
                    '%' . $keyword . '%'
                )
                    ->latest()
                    ->get();
            }
        }

        return view('pages.search', compact(
            'keyword',
            'campaigns',
            'berita',
            'type'
        ));
    }
}
