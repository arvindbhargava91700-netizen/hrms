<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobCategory;
use App\Models\JobTemplate;

class JobTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Job Categories
        $categories = ['IT & Software', 'BPO & Customer Care', 'Delivery & Logistics', 'Sales & Marketing', 'Design'];
        foreach ($categories as $cat) {
            JobCategory::firstOrCreate(['name' => $cat], ['is_active' => true]);
        }

        // 2. Create Software Engineer Template
        $q1 = uniqid('q_');
        $q2 = uniqid('q_');

        JobTemplate::updateOrCreate(
            ['title' => 'Software Engineer'],
            [
                'category' => 'IT & Software',
                'overview' => 'We are looking for a skilled Software Engineer to develop scalable applications.',
                'description' => 'You will be part of a cross-functional team that is responsible for the full software development life cycle. Responsibilities: Write clean code, Review PRs, Design architecture.',
                'default_salary_min' => 50000,
                'default_salary_max' => 100000,
                'job_type' => 'Full-time',
                'is_active' => true,
                'default_screening_questions' => [
                    [
                        'id' => $q1,
                        'question' => 'Do you have Laravel experience?',
                        'type' => 'radio',
                        'required' => 1,
                        'options' => 'Yes, No',
                        'conditional_parent' => '',
                        'conditional_value' => ''
                    ],
                    [
                        'id' => $q2,
                        'question' => 'How many years of Laravel experience do you have?',
                        'type' => 'text',
                        'required' => 1,
                        'options' => '',
                        'conditional_parent' => $q1,
                        'conditional_value' => 'Yes'
                    ],
                    [
                        'id' => uniqid('q_'),
                        'question' => 'Can you work remotely?',
                        'type' => 'radio',
                        'required' => 1,
                        'options' => 'Yes, No',
                        'conditional_parent' => '',
                        'conditional_value' => ''
                    ]
                ]
            ]
        );

        // 3. Create Delivery Executive Template (with conditional questions)
        $dq1 = uniqid('q_');
        $dq2 = uniqid('q_');

        JobTemplate::updateOrCreate(
            ['title' => 'Delivery Executive'],
            [
                'category' => 'Delivery & Logistics',
                'overview' => 'Deliver packages to customers on time. Must have a valid driving license.',
                'description' => '<p>We are looking for a reliable <strong>Delivery Executive</strong> to ensure timely delivery of packages to our customers.</p><ul><li>Pick up items from the warehouse</li><li>Navigate using GPS</li><li>Collect payments on delivery</li></ul>',
                'job_type' => 'Full-time',
                'default_salary_min' => 15000,
                'default_salary_max' => 25000,
                'is_active' => true,
                'default_screening_questions' => [
                    [
                        'id' => $dq1,
                        'question' => 'Do you have a vehicle?',
                        'type' => 'checkbox',
                        'required' => 1,
                        'options' => 'Yes, No',
                        'conditional_parent' => '',
                        'conditional_value' => ''
                    ],
                    [
                        'id' => $dq2,
                        'question' => 'What type of vehicle do you have?',
                        'type' => 'select',
                        'required' => 1,
                        'options' => 'Bike, Scooty, Electric Vehicle',
                        'conditional_parent' => $dq1,
                        'conditional_value' => 'Yes'
                    ],
                    [
                        'id' => uniqid('q_'),
                        'question' => 'Do you have a valid driving license?',
                        'type' => 'radio',
                        'required' => 1,
                        'options' => 'Yes, No',
                        'conditional_parent' => '',
                        'conditional_value' => ''
                    ]
                ]
            ]
        );

        // 4. Create Sales Executive Template
        $sq1 = uniqid('q_');
        JobTemplate::updateOrCreate(
            ['title' => 'Sales Executive'],
            [
                'category' => 'Sales & Marketing',
                'overview' => 'Join our sales team to drive growth and acquire new clients.',
                'description' => 'You will be responsible for outbound sales and closing deals.',
                'default_salary_min' => 30000,
                'default_salary_max' => 60000,
                'job_type' => 'Full-time',
                'is_active' => true,
                'default_screening_questions' => [
                    [
                        'id' => $sq1,
                        'question' => 'Do you have B2B sales experience?',
                        'type' => 'radio',
                        'required' => 1,
                        'options' => 'Yes, No',
                        'conditional_parent' => '',
                        'conditional_value' => ''
                    ]
                ]
            ]
        );
    }
}
