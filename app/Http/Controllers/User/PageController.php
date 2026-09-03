<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\SyaratKetentuan;

class PageController extends Controller
{
    public function syaratKetentuan()
    {
        $terms = SyaratKetentuan::aktif()->get();
        $faqs = Faq::aktif()->get();

        return view('pages.syarat-ketentuan', compact('terms', 'faqs'));
    }

    public function pusatBantuan()
    {
        $faqs = Faq::aktif()->get();

        return view('pages.pusat-bantuan', compact('faqs'));
    }

    public function tentang()
    {
        $faqs = Faq::aktif()->get();

        return view('pages.tentang', compact('faqs'));
    }
}
