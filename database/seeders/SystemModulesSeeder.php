<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemModule;

class SystemModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name' => 'HRMS',
                'slug' => 'hrms',
                'icon' => 'bi-people',
                'description' => 'Complete Human Resource Management System including Staff, Attendance, Leaves, and Payroll.',
                'is_active' => true,
            ],
            [
                'name' => 'CRM',
                'slug' => 'crm',
                'icon' => 'bi-headset',
                'description' => 'Customer Relationship Management for tracking leads, customers, and interactions.',
                'is_active' => true,
            ],
            [
                'name' => 'Accounting',
                'slug' => 'accounting',
                'icon' => 'bi-calculator',
                'description' => 'Advanced financial tracking, invoicing, and expense management.',
                'is_active' => true,
            ],
            [
                'name' => 'Inventory',
                'slug' => 'inventory',
                'icon' => 'bi-box-seam',
                'description' => 'Stock tracking, supplier management, and automated alerts.',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'icon' => 'bi-megaphone',
                'description' => 'Email campaigns, SMS marketing, and promotional tools.',
                'is_active' => true,
            ]
        ];

        foreach ($modules as $module) {
            SystemModule::updateOrCreate(
                ['slug' => $module['slug']],
                $module
            );
        }

        $this->command->info('System Modules seeded successfully!');
    }
}
