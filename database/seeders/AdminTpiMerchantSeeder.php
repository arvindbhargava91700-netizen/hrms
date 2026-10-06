<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TpiMerchant;

class AdminTpiMerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the admin user
        $admin = User::where('role', 'super_admin')->first() ?? User::where('role', 'admin')->first();

        if ($admin) {
            TpiMerchant::updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'tpi_merchant_id' => '9',
                    'tpi_kyc_id' => 'ADMIN_DEFAULT_KYC_ID',
                    'tpi_kyc_status' => 'APPROVED',
                    'details' => [
                        'legal_name' => 'Admin Default Gateway',
                        'business_name' => 'Feetrack Admin',
                        'email' => $admin->email,
                        'contact_number' => '0000000000',
                    ],
                ]
            );
            
            $this->command->info('Admin TPI Merchant details seeded successfully.');
        } else {
            $this->command->error('No admin user found. Cannot seed admin TPI merchant.');
        }
    }
}
