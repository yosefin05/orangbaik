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
                'name'      => 'Tripay',
                'code'      => 'tripay',
                'is_active' => true,
            ],
            [
                'name'      => 'iPaymu',
                'code'      => 'ipaymu',
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
            PaymentGateway::firstOrCreate(
                ['code' => $gateway['code']],
                $gateway
            );
        }

        $this->command->info('PaymentGateway seeded: Midtrans, Tripay, iPaymu, Flip, Transfer Manual');
    }
}
