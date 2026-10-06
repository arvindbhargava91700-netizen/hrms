<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find all partner users to assign these categories/products to
        $partners = User::where('role', 'partner')->get();

        if ($partners->isEmpty()) {
            $this->command->info("No Partner users found. Run DatabaseSeeder first or ensure a Partner exists.");
            return;
        }

        $categories = [
            'Software Subscriptions',
            'Hardware & Devices',
            'Add-on Services',
            'Marketing Materials'
        ];

        foreach ($partners as $partner) {
            $partnerId = $partner->id;
            
            foreach ($categories as $catName) {
                $category = ProductCategory::firstOrCreate([
                    'partner_id' => $partnerId,
                    'name' => $catName,
                ], [
                    'status' => 'active'
                ]);

                // Seed products for each category
                if ($catName === 'Software Subscriptions') {
                    $products = [
                        ['name' => 'Basic Plan (1 Year)', 'amount' => 9999.00],
                        ['name' => 'Premium Plan (1 Year)', 'amount' => 14999.00],
                        ['name' => 'Enterprise Plan (1 Year)', 'amount' => 24999.00],
                    ];
                } elseif ($catName === 'Hardware & Devices') {
                    $products = [
                        ['name' => 'Biometric Fingerprint Scanner', 'amount' => 3500.00],
                        ['name' => 'RFID Card Reader', 'amount' => 2000.00],
                        ['name' => 'Turnstile Gate (Single)', 'amount' => 45000.00],
                    ];
                } elseif ($catName === 'Add-on Services') {
                    $products = [
                        ['name' => 'WhatsApp API Integration', 'amount' => 5000.00],
                        ['name' => 'Custom Domain Setup', 'amount' => 1500.00],
                        ['name' => '10,000 SMS Pack', 'amount' => 2500.00],
                    ];
                } else {
                    $products = [
                        ['name' => 'Standard Gym Flyers (1000 pcs)', 'amount' => 1200.00],
                        ['name' => 'Roll-up Standee', 'amount' => 850.00],
                    ];
                }

                foreach ($products as $prod) {
                    Product::firstOrCreate([
                        'partner_id' => $partnerId,
                        'category_id' => $category->id,
                        'name' => $prod['name'],
                    ], [
                        'amount' => $prod['amount'],
                        'status' => 'active'
                    ]);
                }
            }
        }

        $this->command->info("Product Categories and Products seeded successfully for all partners!");
    }
}
