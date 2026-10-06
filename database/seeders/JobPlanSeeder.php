<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Plan',
                'price' => 0,
                'validity_days' => 7,
                'job_limit' => 1,
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Basic Plan',
                'price' => 99,
                'validity_days' => 30,
                'job_limit' => 5,
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Premium Plan',
                'price' => 299,
                'validity_days' => 90,
                'job_limit' => null, // unlimited
                'is_featured' => true,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\JobPlan::create($plan);
        }
    }
}
