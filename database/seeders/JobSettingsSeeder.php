<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JobSetting;

class JobSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Skills
            ['type' => 'skill', 'name' => 'Communication Skills'],
            ['type' => 'skill', 'name' => 'Broadband Installation'],
            ['type' => 'skill', 'name' => 'Splicing'],
            ['type' => 'skill', 'name' => 'Broadband Servicing'],
            ['type' => 'skill', 'name' => 'Data Entry'],
            ['type' => 'skill', 'name' => 'Team Management'],
            ['type' => 'skill', 'name' => 'Sales'],
            ['type' => 'skill', 'name' => 'Marketing'],
            ['type' => 'skill', 'name' => 'Customer Support'],
            
            // Assets
            ['type' => 'asset', 'name' => 'Two-Wheeler Vehicle'],
            ['type' => 'asset', 'name' => 'Two Wheeler Driving License'],
            ['type' => 'asset', 'name' => 'Smartphone'],
            ['type' => 'asset', 'name' => 'Laptop'],
            ['type' => 'asset', 'name' => 'Four Wheeler'],
            ['type' => 'asset', 'name' => 'Four Wheeler Driving License'],

            // Languages
            ['type' => 'language', 'name' => 'English'],
            ['type' => 'language', 'name' => 'Hindi'],
            ['type' => 'language', 'name' => 'Assamese'],
            ['type' => 'language', 'name' => 'Bengali'],
            ['type' => 'language', 'name' => 'Gujarati'],
            ['type' => 'language', 'name' => 'Kannada'],
            ['type' => 'language', 'name' => 'Malayalam'],
            ['type' => 'language', 'name' => 'Marathi'],
            ['type' => 'language', 'name' => 'Punjabi'],
            ['type' => 'language', 'name' => 'Tamil'],
            ['type' => 'language', 'name' => 'Telugu'],

            // Degrees / Specializations
            ['type' => 'degree', 'name' => 'B.Tech / B.E.', 'depends_on' => 'Graduate'],
            ['type' => 'degree', 'name' => 'M.Tech / M.E.', 'depends_on' => 'Post Graduate'],
            ['type' => 'degree', 'name' => 'BBA', 'depends_on' => 'Graduate'],
            ['type' => 'degree', 'name' => 'MBA / PGDM', 'depends_on' => 'Post Graduate'],
            ['type' => 'degree', 'name' => 'BCA', 'depends_on' => 'Graduate'],
            ['type' => 'degree', 'name' => 'MCA', 'depends_on' => 'Post Graduate'],
            ['type' => 'degree', 'name' => 'B.Sc', 'depends_on' => 'Graduate'],
            ['type' => 'degree', 'name' => 'M.Sc', 'depends_on' => 'Post Graduate'],
            ['type' => 'degree', 'name' => 'B.Com', 'depends_on' => 'Graduate'],
            ['type' => 'degree', 'name' => 'M.Com', 'depends_on' => 'Post Graduate'],
            ['type' => 'degree', 'name' => 'B.A', 'depends_on' => 'Graduate'],
            ['type' => 'degree', 'name' => 'M.A', 'depends_on' => 'Post Graduate'],
            ['type' => 'degree', 'name' => 'Diploma', 'depends_on' => 'Diploma'],
            ['type' => 'degree', 'name' => 'ITI', 'depends_on' => 'ITI'],
            
            // Perks
            ['type' => 'perk', 'name' => 'Health Insurance'],
            ['type' => 'perk', 'name' => 'Life Insurance'],
            ['type' => 'perk', 'name' => 'Paid Time Off'],
            ['type' => 'perk', 'name' => 'Flexible Hours'],
            ['type' => 'perk', 'name' => 'PF / EPF'],
            ['type' => 'perk', 'name' => 'Transport Facility'],
            ['type' => 'perk', 'name' => 'Meal Allowance'],
            ['type' => 'perk', 'name' => 'Work From Home option'],
            
            // Cities
            ['type' => 'city', 'name' => 'Mumbai'],
            ['type' => 'city', 'name' => 'Delhi'],
            ['type' => 'city', 'name' => 'Bengaluru'],
            ['type' => 'city', 'name' => 'Hyderabad'],
            ['type' => 'city', 'name' => 'Ahmedabad'],
            ['type' => 'city', 'name' => 'Chennai'],
            ['type' => 'city', 'name' => 'Kolkata'],
            ['type' => 'city', 'name' => 'Surat'],
            ['type' => 'city', 'name' => 'Pune'],
            ['type' => 'city', 'name' => 'Jaipur'],
            ['type' => 'city', 'name' => 'Lucknow']
        ];

        foreach ($settings as $setting) {
            JobSetting::updateOrCreate(
                ['type' => $setting['type'], 'name' => $setting['name']],
                ['depends_on' => $setting['depends_on'] ?? null]
            );
        }
    }
}
