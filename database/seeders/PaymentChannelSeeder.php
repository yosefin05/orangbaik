<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;
use App\Models\PaymentChannel;

class PaymentChannelSeeder extends Seeder
{
    public function run(): void
    {
        $midtrans = PaymentGateway::where('code', 'midtrans')->first();
        $tripay   = PaymentGateway::where('code', 'tripay')->first();
        $ipaymu   = PaymentGateway::where('code', 'ipaymu')->first();
        $flip     = PaymentGateway::where('code', 'flip')->first();
        $manual   = PaymentGateway::where('code', 'manual')->first();

        if (!$midtrans || !$tripay || !$ipaymu || !$flip || !$manual) {
            $this->command->error('Payment gateways belum lengkap. Jalankan PaymentGatewaySeeder terlebih dahulu.');
            return;
        }

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        PaymentChannel::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $channels = [
            // 1. Gopay
            [
                'payment_gateway_id' => $midtrans->id,
                'name'               => 'Gopay',
                'channel_code'       => 'gopay',
                'account_name'       => null,
                'account_number'     => null,
                'payment_type'       => 'instant',
                'sort_order'         => 1,
                'is_active'          => true,
            ],
            // 2. ShopeePay
            [
                'payment_gateway_id' => $tripay->id,
                'name'               => 'ShopeePay',
                'channel_code'       => 'shopeepay',
                'account_name'       => null,
                'account_number'     => null,
                'payment_type'       => 'instant',
                'sort_order'         => 2,
                'is_active'          => true,
            ],
            // 3. LinkAja
            [
                'payment_gateway_id' => $ipaymu->id,
                'name'               => 'LinkAja',
                'channel_code'       => 'linkaja',
                'account_name'       => null,
                'account_number'     => null,
                'payment_type'       => 'instant',
                'sort_order'         => 3,
                'is_active'          => true,
            ],
            // 4. QRIS
            [
                'payment_gateway_id' => $midtrans->id,
                'name'               => 'QRIS',
                'channel_code'       => 'qris',
                'account_name'       => null,
                'account_number'     => null,
                'payment_type'       => 'instant',
                'sort_order'         => 4,
                'is_active'          => true,
            ],
            // 5. Bank BCA (Manual Transfer)
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank BCA',
                'channel_code'       => 'BCA - 01',
                'account_name'       => 'Dompet Al Quds',
                'account_number'     => '1234567890',
                'payment_type'       => 'transfer',
                'sort_order'         => 5,
                'is_active'          => true,
            ],
            // 6. Bank Syariah (BSI Manual Transfer)
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank Syariah (BSI)',
                'channel_code'       => 'BSI - NM',
                'account_name'       => 'Dompet Al Quds',
                'account_number'     => '7123456789',
                'payment_type'       => 'transfer',
                'sort_order'         => 6,
                'is_active'          => true,
            ],
            // 7. Bank BNI (Manual Transfer)
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank BNI',
                'channel_code'       => 'BNI - Ms',
                'account_name'       => 'Dompet Al Quds',
                'account_number'     => '0123456789',
                'payment_type'       => 'transfer',
                'sort_order'         => 7,
                'is_active'          => true,
            ],
            // 8. Flip Transfer
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Flip Transfer',
                'channel_code'       => 'flip',
                'account_name'       => 'Flip',
                'account_number'     => null,
                'payment_type'       => 'transfer',
                'sort_order'         => 8,
                'is_active'          => true,
            ],
            // 9. Bank Mandiri (Manual Transfer)
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank Mandiri',
                'channel_code'       => 'Mandiri -',
                'account_name'       => 'Dompet Al Quds',
                'account_number'     => '1370012345678',
                'payment_type'       => 'transfer',
                'sort_order'         => 9,
                'is_active'          => true,
            ],
            // 10. Bank BRI (Manual Transfer)
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank BRI',
                'channel_code'       => 'BRI - 001',
                'account_name'       => 'Dompet Al Quds',
                'account_number'     => '001201234567890',
                'payment_type'       => 'transfer',
                'sort_order'         => 10,
                'is_active'          => true,
            ],
            // 11. Bank Mega Syariah (Manual Transfer)
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank Mega Syariah',
                'channel_code'       => '708 90 3',
                'account_name'       => 'Dompet Al Quds',
                'account_number'     => '7089031234',
                'payment_type'       => 'transfer',
                'sort_order'         => 11,
                'is_active'          => true,
            ],
            // 12. Bank Jatim Syariah (Manual Transfer)
            [
                'payment_gateway_id' => $manual->id,
                'name'               => 'Bank Jatim Syariah',
                'channel_code'       => '670 100',
                'account_name'       => 'Dompet Al Quds',
                'account_number'     => '6701001234',
                'payment_type'       => 'transfer',
                'sort_order'         => 12,
                'is_active'          => true,
            ],
            // 13. Bank Syariah (BSI Flip VA)
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank Syariah (BSI)',
                'channel_code'       => 'bsi',
                'account_name'       => 'Flip',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 13,
                'is_active'          => true,
            ],
            // 14. Bank BRI (Flip VA)
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank BRI',
                'channel_code'       => 'bri',
                'account_name'       => 'Flip',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 14,
                'is_active'          => true,
            ],
            // 15. Bank Mandiri (Flip VA)
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank Mandiri',
                'channel_code'       => 'mandiri',
                'account_name'       => 'Flip',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 15,
                'is_active'          => true,
            ],
            // 16. Permata Bank (Flip VA)
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Permata Bank',
                'channel_code'       => 'permata',
                'account_name'       => 'Flip',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 16,
                'is_active'          => true,
            ],
            // 17. Bank BCA (iPaymu VA)
            [
                'payment_gateway_id' => $ipaymu->id,
                'name'               => 'Bank BCA',
                'channel_code'       => 'bca',
                'account_name'       => 'iPaymu',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 17,
                'is_active'          => true,
            ],
            // 18. Bank Muamalat (iPaymu VA)
            [
                'payment_gateway_id' => $ipaymu->id,
                'name'               => 'Bank Muamalat',
                'channel_code'       => 'bmi',
                'account_name'       => 'iPaymu',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 18,
                'is_active'          => true,
            ],
            // 19. Bank CIMB Niaga (Flip VA)
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank CIMB Niaga',
                'channel_code'       => 'cimb',
                'account_name'       => 'Flip',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 19,
                'is_active'          => true,
            ],
            // 20. Bank Artha Graha (iPaymu VA)
            [
                'payment_gateway_id' => $ipaymu->id,
                'name'               => 'Bank Artha Graha',
                'channel_code'       => 'bag',
                'account_name'       => 'iPaymu',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 20,
                'is_active'          => true,
            ],
            // 21. Bank Danamon (Flip VA)
            [
                'payment_gateway_id' => $flip->id,
                'name'               => 'Bank Danamon',
                'channel_code'       => 'danamon',
                'account_name'       => 'Flip',
                'account_number'     => null,
                'payment_type'       => 'va',
                'sort_order'         => 21,
                'is_active'          => true,
            ],
        ];

        foreach ($channels as $channel) {
            PaymentChannel::create($channel);
        }

        $this->command->info('PaymentChannel seeded: 21 channels matching OrangBaik previous configuration');
    }
}
