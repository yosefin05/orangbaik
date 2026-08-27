<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            [
                'name'      => 'Midtrans',
                'code'      => 'midtrans',
                'is_active' => true,
            ],
            [
                'name'      => 'Flip',
                'code'      => 'flip',
                'is_active' => true,
            ],
            [
                'name'      => 'Transfer Manual',
                'code'      => 'manual',
                'is_active' => true,
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::updateOrCreate(
                ['code' => $gateway['code']],
                $gateway
            );
        }

        // Hapus gateway yang tidak digunakan
        PaymentGateway::whereNotIn('code', ['midtrans', 'flip', 'manual'])->delete();

        $this->command->info('PaymentGateway seeded: Midtrans, Flip, dan Transfer Manual (Hanya 2 Gateway API + 1 Manual)');
    }
}
