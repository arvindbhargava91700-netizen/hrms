<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@feetrack.com'],
            [
                'name'               => 'Super Admin',
                'mobile'             => '9000000000',
                'role'               => 'super_admin',
                'status'             => 'active',
                'password'           => Hash::make('Admin@123'),
                'email_verified_at'  => now(),
                'mobile_verified_at' => now(),
            ]
        );
        $admin->assignRole('super_admin');

        // $partner = User::firstOrCreate(
        //     ['email' => 'partner@feetrack.com'],
        //     [
        //         'name'               => 'Demo Partner',
        //         'mobile'             => '9000000001',
        //         'role'               => 'partner',
        //         'status'             => 'active',
        //         'password'           => Hash::make('Partner@123'),
        //         'email_verified_at'  => now(),
        //         'mobile_verified_at' => now(),
        //     ]
        // );
        // $partner->assignRole('partner');

        // $customer = User::firstOrCreate(
        //     ['email' => 'customer@feetrack.com'],
        //     [
        //         'name'               => 'Demo Customer',
        //         'mobile'             => '9000000002',
        //         'role'               => 'customer',
        //         'status'             => 'active',
        //         'password'           => Hash::make('Customer@123'),
        //         'email_verified_at'  => now(),
        //         'mobile_verified_at' => now(),
        //     ]
        // );
        // $customer->assignRole('customer');
    }
}
