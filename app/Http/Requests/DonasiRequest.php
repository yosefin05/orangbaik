<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DonasiRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi yang berlaku.
     */
    public function rules()
    {
        return [
            'nominal' => 'nullable|numeric|min:5000',
            'nominal_lainnya' => 'nullable|numeric|min:5000',
            'nama_donatur' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            'pesan' => 'nullable|string|max:255',
        ];
    }

    /**
     * Pesan error kustom (opsional).
     */
    public function messages(): array
    {
        return [
            'nominal.min' => 'Minimal donasi Rp5.000',
            'nominal_lainnya.min' => 'Minimal donasi Rp5.000',
        ];
    }
}