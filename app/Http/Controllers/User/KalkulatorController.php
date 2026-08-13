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
        $gaji = (int) str_replace('.', '', $request->gaji ?? 0);
        $bonus = (int) str_replace('.', '', $request->bonus ?? 0);
        $totalHarta = $gaji + $bonus;
        $hutang = 0;
        $hartaBersih = $totalHarta - $hutang;
        $nishab = (self::NISHAB_GRAM * self::HARGA_EMAS) / 12;
        $wajib = $hartaBersih >= $nishab;
        $zakat = $wajib ? $hartaBersih * self::TARIF : 0;

        return back()->with([
            'selected_zakat' => $request->jenis,
            'jenis' => 'Zakat Penghasilan',
            'hasil' => $zakat,
            'persentase' => 2.5,
            'dasar' => $hartaBersih,
            'harta' => $totalHarta,
            'hutang' => $hutang,
            'nishab' => $nishab,
            'harga_emas' => self::HARGA_EMAS,
            'dasar_hukum' => 'Zakat penghasilan sebesar 2,5% apabila penghasilan bulanan telah mencapai nisab BAZNAS RI Tahun 2024.',
            'wajib' => $wajib,
        ]);
    }

    // emas
    private function emas($request)
    {
        $gram = (float) $request->gram;
        $totalHarta = $gram * self::HARGA_EMAS;
        $hutang = (int) str_replace('.', '', $request->pengurang ?? 0);
        $hartaBersih = max(0, $totalHarta - $hutang);
        $nishab = self::NISHAB_GRAM * self::HARGA_EMAS;
        $wajib = $hartaBersih >= $nishab;
        $zakat = $wajib ? $hartaBersih * self::TARIF : 0;

        return back()->with([
            'selected_zakat' => $request->jenis,
            'jenis' => 'Zakat Emas',
            'hasil' => $zakat,
            'persentase' => 2.5,
            'dasar' => $hartaBersih,
            'harta' => $totalHarta,
            'hutang' => $hutang,
            'nishab' => $nishab,
            'harga_emas' => self::HARGA_EMAS,
            'dasar_hukum' => 'Zakat emas wajib apabila kepemilikan mencapai nisab 85 gram emas.',
            'wajib' => $wajib,
        ]);
    }

    // tabungan
    private function tabungan($request)
    {
        $saldo = (int) str_replace('.', '', $request->saldo);
        $bunga = (int) str_replace('.', '', $request->bunga ?? 0);
        $totalHarta = $saldo;
        $hutang = $bunga;
        $hartaBersih = max(0, $saldo - $bunga);
        $nishab = self::NISHAB_GRAM * self::HARGA_EMAS;
        $wajib = $hartaBersih >= $nishab;
        $zakat = $wajib ? $hartaBersih * self::TARIF : 0;

        return back()->with([
            'selected_zakat' => $request->jenis,
            'jenis' => 'Zakat Tabungan',
            'hasil' => $zakat,
            'persentase' => 2.5,
            'dasar' => $hartaBersih,
            'harta' => $totalHarta,
            'hutang' => $hutang,
            'nishab' => $nishab,
            'harga_emas' => self::HARGA_EMAS,
            'dasar_hukum' => 'Zakat tabungan dihitung dari saldo bersih yang telah mencapai nisab.',
            'wajib' => $wajib,
        ]);
    }

    // perdagangan
    private function perdagangan($request)
    {
        $modal = (int) str_replace('.', '', $request->modal);
        $untung = (int) str_replace('.', '', $request->untung);
        $piutang = (int) str_replace('.', '', $request->piutang ?? 0);
        $rugi = (int) str_replace('.', '', $request->rugi ?? 0);
        $hutang = (int) str_replace('.', '', $request->hutang ?? 0);
        $totalHarta = $modal + $untung + $piutang;
        $totalHutang = $rugi + $hutang;
        $hartaBersih = max(0, $totalHarta - $totalHutang);
        $nishab = self::NISHAB_GRAM * self::HARGA_EMAS;
        $wajib = $hartaBersih >= $nishab;
        $zakat = $wajib ? $hartaBersih * self::TARIF : 0;

        return back()->with([
            'selected_zakat' => $request->jenis,
            'jenis' => 'Zakat Perniagaan',
            'hasil' => $zakat,
            'persentase' => 2.5,
            'dasar' => $hartaBersih,
            'harta' => $totalHarta,
            'hutang' => $totalHutang,
            'nishab' => $nishab,
            'harga_emas' => self::HARGA_EMAS,
            'dasar_hukum' => 'Zakat perniagaan dihitung dari harta bersih usaha yang telah mencapai nisab.',
            'wajib' => $wajib,
        ]);
    }
}