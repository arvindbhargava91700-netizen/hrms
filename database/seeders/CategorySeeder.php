<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // ─── Fitness / Activity ────────────────────────────────
            [
                'name'         => 'Gym',
                'icon'         => 'bi-heart-pulse',
                'color'        => '#EF4444',
                'has_shifts'   => true,
                'has_trainers' => true,
                'has_rooms'    => false,
                'has_packages' => true,
            ],
            [
                'name'         => 'Dance Center',
                'icon'         => 'bi-music-note-beamed',
                'color'        => '#EC4899',
                'has_shifts'   => true,
                'has_trainers' => true,
                'has_rooms'    => false,
                'has_packages' => true,
            ],
            // [
            //     'name'         => 'Sports Club',
            //     'icon'         => 'bi-trophy',
            //     'color'        => '#F97316',
            //     'has_shifts'   => true,
            //     'has_trainers' => true,
            //     'has_rooms'    => false,
            //     'has_packages' => true,
            // ],

            // ─── Accommodation ─────────────────────────────────────
            [
                'name'         => 'PG / Hostel',
                'icon'         => 'bi-house-door',
                'color'        => '#F59E0B',
                'has_shifts'   => false,
                'has_trainers' => false,
                'has_rooms'    => true,
                'has_packages' => true,
            ],
            // [
            //     'name'         => 'Room Rental',
            //     'icon'         => 'bi-door-open',
            //     'color'        => '#10B981',
            //     'has_shifts'   => false,
            //     'has_trainers' => false,
            //     'has_rooms'    => true,
            //     'has_packages' => true,
            // ],
            // [
            //     'name'         => 'Apartment',
            //     'icon'         => 'bi-building',
            //     'color'        => '#6366F1',
            //     'has_shifts'   => false,
            //     'has_trainers' => false,
            //     'has_rooms'    => true,
            //     'has_packages' => true,
            // ],

            // // ─── Services ──────────────────────────────────────────
            // [
            //     'name'         => 'WiFi Provider',
            //     'icon'         => 'bi-wifi',
            //     'color'        => '#3B82F6',
            //     'has_shifts'   => false,
            //     'has_trainers' => false,
            //     'has_rooms'    => false,
            //     'has_packages' => true,
            // ],
            // [
            //     'name'         => 'Library',
            //     'icon'         => 'bi-book',
            //     'color'        => '#8B5CF6',
            //     'has_shifts'   => false,
            //     'has_trainers' => false,
            //     'has_rooms'    => false,
            //     'has_packages' => true,
            // ],
            // [
            //     'name'         => 'Coaching Institute',
            //     'icon'         => 'bi-mortarboard',
            //     'color'        => '#0EA5E9',
            //     'has_shifts'   => false,
            //     'has_trainers' => false,
            //     'has_rooms'    => false,
            //     'has_packages' => true,
            // ],
            // [
            //     'name'         => 'Coworking Space',
            //     'icon'         => 'bi-laptop',
            //     'color'        => '#06B6D4',
            //     'has_shifts'   => false,
            //     'has_trainers' => false,
            //     'has_rooms'    => true,
            //     'has_packages' => true,
            // ],
        ];

        foreach ($categories as $i => $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name'         => $cat['name'],
                    'icon'         => $cat['icon'],
                    'color'        => $cat['color'],
                    'is_active'    => true,
                    'sort_order'   => $i + 1,
                    'has_shifts'   => $cat['has_shifts'],
                    'has_trainers' => $cat['has_trainers'],
                    'has_rooms'    => $cat['has_rooms'],
                    'has_packages' => $cat['has_packages'],
                ]
            );
        }
    }
}
