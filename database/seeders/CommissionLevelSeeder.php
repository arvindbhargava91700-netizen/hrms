<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommissionLevel;
use App\Models\User;

class CommissionLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the first partner to assign the commission levels to
        $partner = User::where('role', 'partner')->first();

        if (!$partner) {
            $this->command->info('No partner found. Please create a partner first before running this seeder.');
            return;
        }

        $levels = [
            [
                'partner_id' => $partner->id,
                'level_name' => 'Manager (L4)',
                'level_order' => 4,
                'commission_percent' => 3.00,
                'description' => 'Top level management',
            ],
            [
                'partner_id' => $partner->id,
                'level_name' => 'Team Leader (L3)',
                'level_order' => 3,
                'commission_percent' => 2.00,
                'description' => 'Reporting to: Manager',
            ],
            [
                'partner_id' => $partner->id,
                'level_name' => 'Sr Employee (L2)',
                'level_order' => 2,
                'commission_percent' => 3.00,
                'description' => 'Reporting to: Team Leader',
            ],
            [
                'partner_id' => $partner->id,
                'level_name' => 'Junior Employee (L1)',
                'level_order' => 1,
                'commission_percent' => 5.00,
                'description' => 'Reporting to: Sr Employee',
            ],
        ];

        foreach ($levels as $levelData) {
            CommissionLevel::updateOrCreate(
                [
                    'partner_id' => $levelData['partner_id'],
                    'level_name' => $levelData['level_name'],
                ],
                $levelData
            );
        }

        $this->command->info('Commission levels seeded successfully for partner ID: ' . $partner->id);
    }
}
