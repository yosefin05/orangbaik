<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;
use App\Models\PaymentChannel;
use Illuminate\Support\Facades\Schema;

class PaymentChannelSeeder extends Seeder
{
    public function run(): void
    {
        $midtrans = PaymentGateway::where('code', 'midtrans')->first();
        $flip     = PaymentGateway::where('code', 'flip')->first();
        $manual   = PaymentGateway::where('code', 'manual')->first();

        if (!$midtrans || !$flip || !$manual) {
            $this->command->error('Pastikan PaymentGatewaySeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        Schema::disableForeignKeyConstraints();
        PaymentChannel::truncate();
        Schema::enableForeignKeyConstraints();

        $channels = [
            // E-Wallet & QRIS (Midtrans)
            [
                'payment_gateway_id' => $midtrans->id,
                'name'               => 'GoPay',
                'channel_code'       => 'gopay',
                'account_name'       => null,
                'account_number'     => null,
                'payment_type'       => 'instant',
                'sort_order'         => 1,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $midtrans->id,
                'name'               => 'ShopeePay',
                'channel_code'       => 'shopeepay',
                'account_name'       => null,
                'account_number'     => null,
                'payment_type'       => 'instant',
                'sort_order'         => 2,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $midtrans->id,
                'name'               => 'QRIS',
                'channel_code'       => 'qris',
                'account_name'       => null,
                'account_number'     => null,
                'payment_type'       => 'instant',
                'sort_order'         => 3,
                'is_active'          => true,
            ],

            // Transfer Manual (Dompet Al Qur'an)
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank BCA',
                'channel_code'       => 'BCA - 01',
                'account_name'       => "Dompet Al Qur'an",
                'account_number'     => '1234567890',
                'payment_type'       => 'transfer',
                'sort_order'         => 4,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank Syariah (BSI)',
                'channel_code'       => 'BSI - NM',
                'account_name'       => "Dompet Al Qur'an",
                'account_number'     => '7123456789',
                'payment_type'       => 'transfer',
                'sort_order'         => 5,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank BNI',
                'channel_code'       => 'BNI - Ms',
                'account_name'       => "Dompet Al Qur'an",
                'account_number'     => '0123456789',
                'payment_type'       => 'transfer',
                'sort_order'         => 6,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank Mandiri',
                'channel_code'       => 'Mandiri -',
                'account_name'       => "Dompet Al Qur'an",
                'account_number'     => '1370012345678',
                'payment_type'       => 'transfer',
                'sort_order'         => 7,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank BRI',
                'channel_code'       => 'BRI - 001',
                'account_name'       => "Dompet Al Qur'an",
                'account_number'     => '001201234567890',
                'payment_type'       => 'transfer',
                'sort_order'         => 8,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank Mega Syariah',
                'channel_code'       => '708 90 3',
                'account_name'       => "Dompet Al Qur'an",
                'account_number'     => '7089031234',
                'payment_type'       => 'transfer',
                'sort_order'         => 9,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank Jatim Syariah',
                'channel_code'       => '670 100',
                'account_name'       => "Dompet Al Qur'an",
                'account_number'     => '6701001234',
                'payment_type'       => 'transfer',
                'sort_order'         => 10,
                'is_active'          => true,
            ],

            // Virtual Account (Flip)
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank BCA',
                'channel_code'       => 'bca',
                'account_name'       => 'BCA Virtual Account',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 11,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank BNI',
                'channel_code'       => 'bni',
                'account_name'       => 'BNI Virtual Account',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 12,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank BRI',
                'channel_code'       => 'bri',
                'account_name'       => 'BRI Virtual Account',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 13,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank Syariah (BSI)',
                'channel_code'       => 'bsi',
                'account_name'       => 'BSI Virtual Account',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 14,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank Mandiri',
                'channel_code'       => 'mandiri',
                'account_name'       => 'Mandiri Virtual Account',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 15,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Permata Bank',
                'channel_code'       => 'permata',
                'account_name'       => 'Permata Virtual Account',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 16,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank CIMB Niaga',
                'channel_code'       => 'cimb',
                'account_name'       => 'CIMB Virtual Account',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 17,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank Danamon',
                'channel_code'       => 'danamon',
                'account_name'       => 'Danamon Virtual Account',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 18,
                'is_active'          => true,
            ],
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank Muamalat',
                'channel_code'       => 'muamalat',
                'account_name'       => 'Muamalat Virtual Account',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 19,
                'is_active'          => true,
            ],
        ];

        foreach ($channels as $channel) {
            PaymentChannel::create($channel);
        }

        $this->command->info('PaymentChannel seeded: 19 channels configured for Midtrans, Flip, & Dompet Al Qur\'an');
    }
}
