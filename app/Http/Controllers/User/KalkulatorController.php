<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KalkulatorController extends Controller
{
    const HARGA_EMAS = 968385;
    const NISHAB_GRAM = 85;
    const TARIF = 0.025;
    public function index()
    {
        return view('pages.kalkulator');
    }

    public function calculate(Request $request)
    {
        switch ($request->jenis) {

            case 'penghasilan':
                return $this->penghasilan($request);

            case 'emas':
                return $this->emas($request);

            case 'tabungan':
                return $this->tabungan($request);

            case 'perdagangan':
                return $this->perdagangan($request);

        }
    }

    // penghasilan
    private function penghasilan($request)
    {

        $gaji = $request->gaji ?? 0;
        $bonus = $request->bonus ?? 0;


        $bruto = $gaji + $bonus;


        $nishabBulanan =
            (self::NISHAB_GRAM * self::HARGA_EMAS) / 12;


        $wajib = $bruto >= $nishabBulanan;


        $zakat = $wajib
            ? $bruto * self::TARIF
            : 0;


        return back()->with([

            'hasil' => $zakat,

            'jenis' => 'Zakat Penghasilan',

            'wajib' => $wajib,

            'dasar' => $bruto,

            'persentase' => 2.5,


            'detail' => [
                'Pendapatan Pokok' => $gaji,
                'Pendapatan Lain' => $bonus,
                'Total Bruto' => $bruto,
                'Nishab' => $nishabBulanan
            ],


            'dasar_hukum' =>
                'SK BAZNAS RI No. 01 Tahun 2024'

        ]);

    }

    // emas
    private function emas($request)
    {

        $gram = $request->gram;

        $harga =
            $request->harga_emas
            ?? self::HARGA_EMAS;


        $nilai =
            $gram * $harga;


        $wajib =
            $gram >= 85;


        $zakat =
            $wajib
            ? $nilai * self::TARIF
            : 0;


        return back()->with([

            'hasil' => $zakat,

            'jenis' => 'Zakat Emas',

            'wajib' => $wajib,


            'detail' => [

                'Jumlah emas' => $gram . ' gram',

                'Harga emas' => $harga,

                'Nilai emas' => $nilai,

                'Nishab' => '85 gram'

            ]

        ]);

    }

    // tabungan
    private function tabungan($request)
    {

        $saldo = $request->saldo;

        $bunga = $request->bunga ?? 0;


        $bersih =
            max(0, $saldo - $bunga);


        $nishab =
            self::NISHAB_GRAM *
            self::HARGA_EMAS;


        $wajib =
            $bersih >= $nishab;


        $zakat =
            $wajib
            ? $bersih * self::TARIF
            : 0;


        return back()->with([

            'hasil' => $zakat,

            'jenis' => 'Zakat Tabungan',

            'wajib' => $wajib,


            'detail' => [

                'Saldo' => $saldo,

                'Bunga' => $bunga,

                'Saldo Bersih' => $bersih,

                'Nishab' => $nishab

            ]

        ]);

    }

    // perdagangan
    private function perdagangan($request)
    {

        $dasar =
            (
                $request->modal +
                $request->untung +
                $request->piutang
            )
            -
            (
                $request->rugi +
                $request->hutang
            );


        $dasar = max(0, $dasar);


        $nishab =
            self::NISHAB_GRAM *
            self::HARGA_EMAS;


        $wajib =
            $dasar >= $nishab;


        $zakat =
            $wajib
            ?
            $dasar * self::TARIF
            :
            0;


        return back()->with([

            'hasil' => $zakat,

            'jenis' => 'Zakat Perdagangan',

            'wajib' => $wajib,

            'detail' => [

                'Dasar Zakat' => $dasar,

                'Nishab' => $nishab

            ]

        ]);

    }
}