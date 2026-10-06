<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\PipelineStage;

class PipelineStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all partner users
        $partners = User::where('role', 'partner')->get();

        foreach ($partners as $partner) {
            // Check if pipeline stages already exist for this partner
            $existingCount = PipelineStage::where('partner_id', $partner->id)->count();
            
            if ($existingCount == 0) {
                // Get the departments for this partner
                $departments = Department::where('partner_id', $partner->id)->orderBy('id')->get();
                
                $orderIndex = 1;
                foreach ($departments as $department) {
                    PipelineStage::create([
                        'partner_id' => $partner->id,
                        'name' => $department->name . ' Approval', // or just use department name
                        'department_id' => $department->id,
                        'order_index' => $orderIndex,
                    ]);
                    
                    $orderIndex++;
                }
            }
        }
    }
}
