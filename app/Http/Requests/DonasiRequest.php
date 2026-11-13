<?php

namespace App\Http\Requests;

use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;

class DonasiRequest extends FormRequest
{
    private ?int $resolvedMinimumDonation = null;

    /**
     * Tentukan apakah user diizinkan membuat request ini.
     * Guest donation diizinkan (user_id nullable di donasi).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalisasi data sebelum validasi:
     * - merge nominal_lainnya → nominal jika custom diisi
     * - merge anonymous_donor/anonymous_message → is_anonim
     */
    protected function prepareForValidation(): void
    {
        $customNominal = (int) $this->input('nominal_lainnya', 0);
        $presetNominal = (int) $this->input('nominal', 0);

        // Custom nominal override preset jika diisi dan > 0
        $effectiveNominal = ($customNominal > 0) ? $customNominal : $presetNominal;

        // is_anonim = true jika salah satu checkbox anonymous dicentang
        $isAnonim = $this->boolean('anonymous_donor') || $this->boolean('anonymous_message');

        $this->merge([
            'nominal'   => $effectiveNominal > 0 ? $effectiveNominal : null,
            'is_anonim' => $isAnonim,
        ]);
    }

    /**
     * Aturan validasi.
     */
    public function rules(): array
    {
        $minDonasi = $this->minimumDonation();

        return [
            'payment_channel_id' => 'required|integer|exists:payment_channels,id',
            'nominal'            => 'required|integer|min:' . $minDonasi,
            'nominal_lainnya'    => 'nullable|integer|min:' . $minDonasi,
            'nama_donatur'       => 'nullable|string|max:100',
            'no_hp'              => 'required|string|max:20',
            'pesan'              => 'nullable|string|max:255',
            'is_anonim'          => 'boolean',
        ];
    }

    /**
     * Pesan error kustom.
     */
    public function messages(): array
    {
        $minimumLabel = 'Rp' . number_format($this->minimumDonation(), 0, ',', '.');

        return [
            'payment_channel_id.required' => 'Silakan pilih metode pembayaran.',
            'payment_channel_id.exists'   => 'Metode pembayaran tidak valid.',
            'nominal.required'            => 'Silakan masukkan nominal donasi.',
            'nominal.min'                 => 'Minimal donasi ' . $minimumLabel . '.',
            'nominal_lainnya.min'         => 'Minimal donasi ' . $minimumLabel . '.',
            'no_hp.required'              => 'Nomor telepon wajib diisi.',
        ];
    }

    private function minimumDonation(): int
    {
        if ($this->resolvedMinimumDonation !== null) {
            return $this->resolvedMinimumDonation;
        }

        $paymentMinimum = (int) config('payment.minimum_amount', 1000);
        $campaignSlug = $this->route('slug');
        $campaignMinimum = 1000;

        if ($campaignSlug) {
            $campaignMinimum = (int) (Campaign::query()
                ->where('slug', $campaignSlug)
                ->orWhere('custom_slug', $campaignSlug)
                ->value('minimal_donasi') ?? 1000);
        }

        return $this->resolvedMinimumDonation = max($paymentMinimum, $campaignMinimum);
    }
}