<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\Floor;
use App\Models\ListingShift;
use App\Models\ListingTrainer;
use App\Models\Listing;
use App\Models\ListingMeta;
use App\Models\Package;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ────────────────────────────────────────────────────
        $partner = User::firstOrCreate(
            ['email' => 'partner@feetrack.com'],
            [
                'name'               => 'Demo Partner',
                'mobile'             => '9876543210',
                'password'           => Hash::make('Partner@123'),
                'role'               => 'partner',
                'status'             => 'active',
                'email_verified_at'  => now(),
                'mobile_verified_at' => now(),
            ]
        );
        if (!$partner->hasRole('partner')) $partner->assignRole('partner');

        $customer = User::firstOrCreate(
            ['email' => 'customer@feetrack.com'],
            [
                'name'               => 'Demo Customer',
                'mobile'             => '8888888888',
                'password'           => Hash::make('Customer@123'),
                'role'               => 'customer',
                'status'             => 'active',
                'email_verified_at'  => now(),
                'mobile_verified_at' => now(),
            ]
        );
        if (!$customer->hasRole('customer')) $customer->assignRole('customer');

        // ── Custom Fields ────────────────────────────────────────────
        $this->seedCustomFields();

        // ════════════════════════════════════════════════════════════
        //  GYM
        // ════════════════════════════════════════════════════════════
        $gymCat = Category::where('name', 'Gym')->first();
        if ($gymCat) {
            $gym = Listing::updateOrCreate(
                ['title' => "Gold's Fitness Center"],
                [
                    'partner_id'  => $partner->id,
                    'category_id' => $gymCat->id,
                    'description' => "State-of-the-art gym with premium equipment, certified trainers, steam room, and flexible membership plans for all fitness levels.",
                    'address'     => '12 Fitness Avenue, Andheri West, Mumbai - 400058',
                    'lat'         => 19.13578320,
                    'lng'         => 72.82769080,
                    'status'      => 'approved',
                ]
            );

            // Packages
            Package::updateOrCreate(['listing_id' => $gym->id, 'name' => 'Monthly Basic'],
                ['room_id' => null, 'occupancy_type' => 'standard', 'duration_days' => 30,  'price' => 1200, 'type' => 'monthly',   'features' => ['Cardio Area', 'Free Weights', 'Locker']]);
            Package::updateOrCreate(['listing_id' => $gym->id, 'name' => 'Quarterly Plus'],
                ['room_id' => null, 'occupancy_type' => 'standard', 'duration_days' => 90,  'price' => 3200, 'type' => 'quarterly', 'features' => ['Cardio', 'Weights', 'Locker', 'Steam Room']]);
            Package::updateOrCreate(['listing_id' => $gym->id, 'name' => 'Half-Yearly Pro'],
                ['room_id' => null, 'occupancy_type' => 'standard', 'duration_days' => 180, 'price' => 5500, 'type' => 'half_yearly','features' => ['All Access', '2 Personal Sessions', 'Diet Plan']]);
            Package::updateOrCreate(['listing_id' => $gym->id, 'name' => 'Annual Premium'],
                ['room_id' => null, 'occupancy_type' => 'standard', 'duration_days' => 365, 'price' => 9000, 'type' => 'yearly',    'features' => ['Unlimited Access', 'Personal Trainer 4x/month', 'Nutrition Plan', 'Locker']]);

            // Shifts
            ListingShift::firstOrCreate(['listing_id' => $gym->id, 'shift_name' => 'morning'],
                ['start_time' => '05:30', 'end_time' => '10:00', 'max_members' => 40, 'fee' => 800]);
            ListingShift::firstOrCreate(['listing_id' => $gym->id, 'shift_name' => 'evening'],
                ['start_time' => '16:00', 'end_time' => '20:00', 'max_members' => 50, 'fee' => 900]);
            ListingShift::firstOrCreate(['listing_id' => $gym->id, 'shift_name' => 'night'],
                ['start_time' => '20:00', 'end_time' => '23:00', 'max_members' => 25, 'fee' => 700]);

            // Staff / Trainers
            ListingTrainer::firstOrCreate(['listing_id' => $gym->id, 'name' => 'Rahul Sharma'],
                ['specialization' => 'Strength & Conditioning', 'experience_years' => 6]);
            ListingTrainer::firstOrCreate(['listing_id' => $gym->id, 'name' => 'Priya Verma'],
                ['specialization' => 'Yoga & Flexibility',      'experience_years' => 4]);
            ListingTrainer::firstOrCreate(['listing_id' => $gym->id, 'name' => 'Arjun Mehta'],
                ['specialization' => 'Cardio & Nutrition',      'experience_years' => 8]);

            $this->saveMeta($gym, $gymCat, [
                'Equipment Type'            => 'Treadmill, Elliptical, Free Weights, Cable Machines, Rowing Machine',
                'Personal Trainer Available' => '1',
            ]);
        }

        // ════════════════════════════════════════════════════════════
        //  DANCE CENTER
        // ════════════════════════════════════════════════════════════
        $danceCat = Category::where('name', 'Dance Center')->first();
        if ($danceCat) {
            $dance = Listing::updateOrCreate(
                ['title' => 'Rhythm Dance Studio'],
                [
                    'partner_id'  => $partner->id,
                    'category_id' => $danceCat->id,
                    'description' => "Premier dance studio offering classical, contemporary, and hip-hop dance forms for all age groups with professional certified instructors.",
                    'address'     => '44 Arts Lane, Bandra West, Mumbai - 400050',
                    'lat'         => 19.05990000,
                    'lng'         => 72.83570000,
                    'status'      => 'approved',
                ]
            );

            // Packages
            Package::updateOrCreate(['listing_id' => $dance->id, 'name' => 'Monthly – Single Form'],
                ['room_id' => null, 'occupancy_type' => 'standard', 'duration_days' => 30,  'price' => 2000, 'type' => 'monthly',   'features' => ['1 Dance Style', '8 Sessions/month']]);
            Package::updateOrCreate(['listing_id' => $dance->id, 'name' => 'Monthly – Combo'],
                ['room_id' => null, 'occupancy_type' => 'standard', 'duration_days' => 30,  'price' => 3200, 'type' => 'monthly',   'features' => ['2 Dance Styles', '16 Sessions/month']]);
            Package::updateOrCreate(['listing_id' => $dance->id, 'name' => 'Quarterly All Access'],
                ['room_id' => null, 'occupancy_type' => 'standard', 'duration_days' => 90,  'price' => 5500, 'type' => 'quarterly', 'features' => ['All Dance Forms', 'Stage Performance Access']]);
            Package::updateOrCreate(['listing_id' => $dance->id, 'name' => 'Annual Pro'],
                ['room_id' => null, 'occupancy_type' => 'standard', 'duration_days' => 365, 'price' => 18000,'type' => 'yearly',    'features' => ['All Forms', 'Solo Coaching', 'Costume Allowance', 'Stage Shows']]);

            // Shifts
            ListingShift::firstOrCreate(['listing_id' => $dance->id, 'shift_name' => 'morning'],
                ['start_time' => '07:00', 'end_time' => '09:30', 'max_members' => 25, 'fee' => 1000]);
            ListingShift::firstOrCreate(['listing_id' => $dance->id, 'shift_name' => 'evening'],
                ['start_time' => '17:00', 'end_time' => '20:00', 'max_members' => 30, 'fee' => 1200]);

            // Staff / Trainers
            ListingTrainer::firstOrCreate(['listing_id' => $dance->id, 'name' => 'Anita Desai'],
                ['specialization' => 'Classical & Contemporary', 'experience_years' => 5]);
            ListingTrainer::firstOrCreate(['listing_id' => $dance->id, 'name' => 'Karan Singh'],
                ['specialization' => 'Hip-Hop & Salsa',          'experience_years' => 7]);
            ListingTrainer::firstOrCreate(['listing_id' => $dance->id, 'name' => 'Meera Joshi'],
                ['specialization' => 'Bharatnatyam & Folk',      'experience_years' => 10]);

            $this->saveMeta($dance, $danceCat, [
                'Dance Styles Offered' => 'Hip-Hop, Classical, Contemporary, Salsa, Bharatnatyam',
                'Age Group'            => 'Kids (5+), Teens, Adults',
            ]);
        }

        // ════════════════════════════════════════════════════════════
        //  PG / HOSTEL
        // ════════════════════════════════════════════════════════════
        $pgCat = Category::where('name', 'PG / Hostel')->first();
        if ($pgCat) {
            $pg = Listing::updateOrCreate(
                ['title' => 'Sunrise Boys PG'],
                [
                    'partner_id'  => $partner->id,
                    'category_id' => $pgCat->id,
                    'description' => "Fully furnished, secure PG accommodation with AC rooms, high-speed WiFi, hygienic food service, laundry, and 24/7 security.",
                    'address'     => '45 Residency Road, Koramangala 4th Block, Bangalore - 560034',
                    'lat'         => 12.93453320,
                    'lng'         => 77.62657900,
                    'status'      => 'approved',
                ]
            );

            // Floors & Rooms
            $f0 = Floor::updateOrCreate(['listing_id' => $pg->id, 'name' => 'Ground Floor'], ['floor_number' => 0]);
            $f1 = Floor::updateOrCreate(['listing_id' => $pg->id, 'name' => '1st Floor'],    ['floor_number' => 1]);
            $f2 = Floor::updateOrCreate(['listing_id' => $pg->id, 'name' => '2nd Floor'],    ['floor_number' => 2]);

            $pgRooms = [
                Room::updateOrCreate(['floor_id' => $f0->id, 'room_number' => 'G01'], ['room_type' => 'double', 'capacity' => 2, 'available_beds' => 1, 'per_bed_rent' => 7000,  'full_rent' => 13000, 'security_deposit' => 14000]),
                Room::updateOrCreate(['floor_id' => $f0->id, 'room_number' => 'G02'], ['room_type' => 'triple', 'capacity' => 3, 'available_beds' => 2, 'per_bed_rent' => 6000,  'full_rent' => 17000, 'security_deposit' => 12000]),
                Room::updateOrCreate(['floor_id' => $f1->id, 'room_number' => '101'], ['room_type' => 'single', 'capacity' => 1, 'available_beds' => 1, 'per_bed_rent' => 9000,  'full_rent' => 9000,  'security_deposit' => 9000]),
                Room::updateOrCreate(['floor_id' => $f1->id, 'room_number' => '102'], ['room_type' => 'double', 'capacity' => 2, 'available_beds' => 0, 'per_bed_rent' => 7500,  'full_rent' => 14000, 'security_deposit' => 15000]),
                Room::updateOrCreate(['floor_id' => $f1->id, 'room_number' => '103'], ['room_type' => 'triple', 'capacity' => 3, 'available_beds' => 3, 'per_bed_rent' => 6200,  'full_rent' => 16000, 'security_deposit' => 12000]),
                Room::updateOrCreate(['floor_id' => $f2->id, 'room_number' => '201'], ['room_type' => 'double', 'capacity' => 2, 'available_beds' => 2, 'per_bed_rent' => 8000,  'full_rent' => 15000, 'security_deposit' => 16000]),
                Room::updateOrCreate(['floor_id' => $f2->id, 'room_number' => '202'], ['room_type' => 'single', 'capacity' => 1, 'available_beds' => 1, 'per_bed_rent' => 9500,  'full_rent' => 9500,  'security_deposit' => 9500]),
            ];

            foreach ($pgRooms as $room) {
                Package::updateOrCreate(
                    ['listing_id' => $pg->id, 'room_id' => $room->id, 'name' => 'Room ' . $room->room_number . ' – Per Bed'],
                    ['occupancy_type' => 'single',    'duration_days' => 30, 'price' => $room->per_bed_rent, 'type' => 'monthly', 'features' => ['WiFi', 'Water', 'Electricity', 'Maintenance']]
                );
                Package::updateOrCreate(
                    ['listing_id' => $pg->id, 'room_id' => $room->id, 'name' => 'Room ' . $room->room_number . ' – Full Room'],
                    ['occupancy_type' => 'full_room', 'duration_days' => 30, 'price' => $room->full_rent,    'type' => 'monthly', 'features' => ['Full Room', 'WiFi', 'Food', 'Laundry', 'Housekeeping']]
                );
            }

            $this->saveMeta($pg, $pgCat, ['AC/Non-AC' => 'AC', 'Food Included' => '1', 'Deposit Amount' => '15000', 'Gender' => 'Boys']);
        }

        // ════════════════════════════════════════════════════════════
        //  ROOM RENTAL
        // ════════════════════════════════════════════════════════════
        $roomCat = Category::where('name', 'Room Rental')->first();
        if ($roomCat) {
            $rental = Listing::updateOrCreate(
                ['title' => 'Green Valley Room Rentals'],
                [
                    'partner_id'  => $partner->id,
                    'category_id' => $roomCat->id,
                    'description' => "Well-maintained, furnished rooms available for short and long-term rental. Ideal for working professionals and students. Includes WiFi and utilities.",
                    'address'     => '78 MG Road, Indiranagar, Bangalore - 560038',
                    'lat'         => 12.97190000,
                    'lng'         => 77.64090000,
                    'status'      => 'approved',
                ]
            );

            $rf1 = Floor::updateOrCreate(['listing_id' => $rental->id, 'name' => '1st Floor'], ['floor_number' => 1]);
            $rf2 = Floor::updateOrCreate(['listing_id' => $rental->id, 'name' => '2nd Floor'], ['floor_number' => 2]);

            $rentalRooms = [
                Room::updateOrCreate(['floor_id' => $rf1->id, 'room_number' => 'R-101'], ['room_type' => 'single', 'capacity' => 1, 'available_beds' => 1, 'per_bed_rent' => 8000,  'full_rent' => 8000,  'security_deposit' => 16000]),
                Room::updateOrCreate(['floor_id' => $rf1->id, 'room_number' => 'R-102'], ['room_type' => 'single', 'capacity' => 1, 'available_beds' => 1, 'per_bed_rent' => 9000,  'full_rent' => 9000,  'security_deposit' => 18000]),
                Room::updateOrCreate(['floor_id' => $rf2->id, 'room_number' => 'R-201'], ['room_type' => 'double', 'capacity' => 2, 'available_beds' => 2, 'per_bed_rent' => 7000,  'full_rent' => 13000, 'security_deposit' => 15000]),
                Room::updateOrCreate(['floor_id' => $rf2->id, 'room_number' => 'R-202'], ['room_type' => 'double', 'capacity' => 2, 'available_beds' => 1, 'per_bed_rent' => 7500,  'full_rent' => 14000, 'security_deposit' => 16000]),
            ];

            foreach ($rentalRooms as $room) {
                Package::updateOrCreate(
                    ['listing_id' => $rental->id, 'room_id' => $room->id, 'name' => 'Room ' . $room->room_number . ' – Monthly'],
                    ['occupancy_type' => 'full_room', 'duration_days' => 30, 'price' => $room->full_rent, 'type' => 'monthly', 'features' => ['WiFi', 'Water', 'Electricity Included']]
                );
            }

            $this->saveMeta($rental, $roomCat, ['AC/Non-AC' => 'AC', 'Food Included' => '0', 'Deposit Amount' => '18000', 'Furnishing' => 'Semi-Furnished']);
        }

        // ════════════════════════════════════════════════════════════
        //  APARTMENT
        // ════════════════════════════════════════════════════════════
        $aptCat = Category::where('name', 'Apartment')->first();
        if ($aptCat) {
            $apt = Listing::updateOrCreate(
                ['title' => 'Skyline Residency'],
                [
                    'partner_id'  => $partner->id,
                    'category_id' => $aptCat->id,
                    'description' => "Modern fully-furnished apartment complex with 1BHK, 2BHK, and 3BHK units. Includes gym, swimming pool, parking, and 24/7 security.",
                    'address'     => '10 Palm Grove Drive, Whitefield, Bangalore - 560066',
                    'lat'         => 12.96820000,
                    'lng'         => 77.74980000,
                    'status'      => 'approved',
                ]
            );

            $af1 = Floor::updateOrCreate(['listing_id' => $apt->id, 'name' => 'Block A – Floor 1'], ['floor_number' => 1]);
            $af2 = Floor::updateOrCreate(['listing_id' => $apt->id, 'name' => 'Block A – Floor 2'], ['floor_number' => 2]);
            $af3 = Floor::updateOrCreate(['listing_id' => $apt->id, 'name' => 'Block B – Floor 1'], ['floor_number' => 3]);

            $aptRooms = [
                Room::updateOrCreate(['floor_id' => $af1->id, 'room_number' => 'A-101'], ['room_type' => 'single', 'capacity' => 2, 'available_beds' => 2, 'per_bed_rent' => 12000, 'full_rent' => 22000, 'security_deposit' => 44000]),
                Room::updateOrCreate(['floor_id' => $af1->id, 'room_number' => 'A-102'], ['room_type' => 'double', 'capacity' => 4, 'available_beds' => 0, 'per_bed_rent' => 9000,  'full_rent' => 32000, 'security_deposit' => 64000]),
                Room::updateOrCreate(['floor_id' => $af2->id, 'room_number' => 'A-201'], ['room_type' => 'single', 'capacity' => 2, 'available_beds' => 2, 'per_bed_rent' => 13000, 'full_rent' => 24000, 'security_deposit' => 48000]),
                Room::updateOrCreate(['floor_id' => $af2->id, 'room_number' => 'A-202'], ['room_type' => 'triple', 'capacity' => 6, 'available_beds' => 3, 'per_bed_rent' => 8500,  'full_rent' => 48000, 'security_deposit' => 96000]),
                Room::updateOrCreate(['floor_id' => $af3->id, 'room_number' => 'B-101'], ['room_type' => 'double', 'capacity' => 4, 'available_beds' => 4, 'per_bed_rent' => 10000, 'full_rent' => 38000, 'security_deposit' => 76000]),
            ];

            foreach ($aptRooms as $room) {
                Package::updateOrCreate(
                    ['listing_id' => $apt->id, 'room_id' => $room->id, 'name' => 'Unit ' . $room->room_number . ' – Monthly Rent'],
                    ['occupancy_type' => 'full_room', 'duration_days' => 30, 'price' => $room->full_rent, 'type' => 'monthly', 'features' => ['Gym Access', 'Pool', 'Parking', 'Security', 'Power Backup']]
                );
                Package::updateOrCreate(
                    ['listing_id' => $apt->id, 'room_id' => $room->id, 'name' => 'Unit ' . $room->room_number . ' – 11 Month Lease'],
                    ['occupancy_type' => 'full_room', 'duration_days' => 330, 'price' => $room->full_rent * 10, 'type' => 'yearly', 'features' => ['All Monthly Inclusions', '1 Month Free', 'Agreement Provided']]
                );
            }

            $this->saveMeta($apt, $aptCat, [
                'Furnishing Type'   => 'Fully Furnished',
                'Parking Available' => '1',
                'Deposit Amount'    => '50000',
                'Pet Friendly'      => '0',
            ]);
        }

        // ════════════════════════════════════════════════════════════
        //  OTHER CATEGORIES – lightweight demo listings
        // ════════════════════════════════════════════════════════════
        $others = [
            'WiFi Provider'     => ['title' => 'SpeedNet Broadband',   'desc' => 'High-speed fiber WiFi plans for homes and PGs.',         'address' => 'Hyderabad',   'lat' => 17.4432, 'lng' => 78.3779, 'price' => 599,  'fields' => ['Bandwidth (Mbps)' => '100', 'Data Limit (GB)' => 'Unlimited']],
            'Library'           => ['title' => 'Knowledge Hub Library', 'desc' => 'Quiet reading space with 10,000+ books and fast WiFi.',   'address' => 'Pune',        'lat' => 18.5204, 'lng' => 73.8567, 'price' => 800,  'fields' => ['Number of Seats' => '60', 'WiFi Available' => '1']],
            'Coaching Institute'=> ['title' => 'BrightFuture Academy',  'desc' => 'Expert coaching for JEE, NEET, and UPSC aspirants.',     'address' => 'Delhi',       'lat' => 28.6139, 'lng' => 77.2090, 'price' => 2500, 'fields' => ['Subjects Offered' => 'Physics, Chemistry, Maths, Biology', 'Batch Timings' => '7am–9am, 5pm–7pm']],
            'Sports Club'       => ['title' => 'Champions Sports Club', 'desc' => 'Multi-sport facility with outdoor courts and coaching.',   'address' => 'Chennai',     'lat' => 13.0827, 'lng' => 80.2707, 'price' => 1500, 'fields' => ['Sports Available' => 'Cricket, Badminton, Football, Tennis', 'Court Booking Info' => 'Hourly & Monthly booking available']],
            'Coworking Space'   => ['title' => 'HiveDesk Cowork',       'desc' => 'Modern coworking space with dedicated desks and pods.',   'address' => 'Hyderabad',   'lat' => 17.3850, 'lng' => 78.4867, 'price' => 3500, 'fields' => ['Number of Seats' => '80', 'WiFi Available' => '1']],
        ];

        foreach ($others as $catName => $data) {
            $cat = Category::where('name', $catName)->first();
            if (!$cat) continue;

            $listing = Listing::updateOrCreate(
                ['title' => $data['title']],
                [
                    'partner_id'  => $partner->id,
                    'category_id' => $cat->id,
                    'description' => $data['desc'],
                    'address'     => $data['address'],
                    'lat'         => $data['lat'],
                    'lng'         => $data['lng'],
                    'status'      => 'approved',
                ]
            );

            Package::updateOrCreate(
                ['listing_id' => $listing->id, 'name' => 'Standard Monthly'],
                ['room_id' => null, 'occupancy_type' => 'standard', 'duration_days' => 30, 'price' => $data['price'], 'type' => 'monthly', 'features' => ['Full Access']]
            );

            $this->saveMeta($listing, $cat, $data['fields']);
        }

        // ════════════════════════════════════════════════════════════
        //  COUPONS & REVIEWS (For API Testing)
        // ════════════════════════════════════════════════════════════
        \App\Models\Coupon::firstOrCreate(
            ['code' => 'WELCOME50'],
            ['title' => 'Flat ₹50 Off', 'description' => 'Get flat ₹50 off on your first booking.', 'type' => 'flat', 'value' => 50, 'min_amount' => 500]
        );
        \App\Models\Coupon::firstOrCreate(
            ['code' => 'SAVE20'],
            ['title' => '20% Off', 'description' => 'Get 20% off up to ₹200 on premium plans.', 'type' => 'percent', 'value' => 20, 'min_amount' => 1000, 'max_discount' => 200]
        );

        $listings = Listing::approved()->get();
        foreach ($listings as $listing) {
            \App\Models\ListingReview::firstOrCreate([
                'listing_id' => $listing->id,
                'customer_id' => $customer->id,
            ], [
                'rating' => rand(4, 5),
                'comment' => 'Great place! Really enjoyed the experience and the facilities are top-notch.',
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    //  Custom Field Schema
    // ─────────────────────────────────────────────────────────────────
    private function seedCustomFields(): void
    {
        $schema = [
            'Gym' => [
                ['label' => 'Equipment Type',            'field_type' => 'text',     'is_required' => true],
                ['label' => 'Personal Trainer Available', 'field_type' => 'checkbox', 'is_required' => false],
            ],
            'Dance Center' => [
                ['label' => 'Dance Styles Offered', 'field_type' => 'text', 'is_required' => true],
                ['label' => 'Age Group',            'field_type' => 'text', 'is_required' => false],
            ],
            'PG / Hostel' => [
                ['label' => 'AC/Non-AC',      'field_type' => 'text',     'is_required' => true],
                ['label' => 'Food Included',  'field_type' => 'checkbox', 'is_required' => true],
                ['label' => 'Deposit Amount', 'field_type' => 'number',   'is_required' => true],
                ['label' => 'Gender',         'field_type' => 'text',     'is_required' => true],
            ],
            'Room Rental' => [
                ['label' => 'AC/Non-AC',      'field_type' => 'text',     'is_required' => true],
                ['label' => 'Food Included',  'field_type' => 'checkbox', 'is_required' => false],
                ['label' => 'Deposit Amount', 'field_type' => 'number',   'is_required' => true],
                ['label' => 'Furnishing',     'field_type' => 'text',     'is_required' => false],
            ],
            'Apartment' => [
                ['label' => 'Furnishing Type',   'field_type' => 'text',     'is_required' => true],
                ['label' => 'Parking Available', 'field_type' => 'checkbox', 'is_required' => false],
                ['label' => 'Deposit Amount',    'field_type' => 'number',   'is_required' => true],
                ['label' => 'Pet Friendly',      'field_type' => 'checkbox', 'is_required' => false],
            ],
            'WiFi Provider' => [
                ['label' => 'Bandwidth (Mbps)', 'field_type' => 'number', 'is_required' => true],
                ['label' => 'Data Limit (GB)',  'field_type' => 'text',   'is_required' => false],
            ],
            'Library' => [
                ['label' => 'Number of Seats', 'field_type' => 'number',   'is_required' => true],
                ['label' => 'WiFi Available',  'field_type' => 'checkbox', 'is_required' => false],
            ],
            'Coaching Institute' => [
                ['label' => 'Subjects Offered', 'field_type' => 'text', 'is_required' => true],
                ['label' => 'Batch Timings',    'field_type' => 'text', 'is_required' => false],
            ],
            'Sports Club' => [
                ['label' => 'Sports Available',  'field_type' => 'text', 'is_required' => true],
                ['label' => 'Court Booking Info','field_type' => 'text', 'is_required' => false],
            ],
            'Coworking Space' => [
                ['label' => 'Number of Seats', 'field_type' => 'number',   'is_required' => true],
                ['label' => 'WiFi Available',  'field_type' => 'checkbox', 'is_required' => false],
            ],
        ];

        foreach ($schema as $catName => $fields) {
            $cat = Category::where('name', $catName)->first();
            if (!$cat) continue;
            foreach ($fields as $idx => $field) {
                CustomField::firstOrCreate(
                    ['category_id' => $cat->id, 'label' => $field['label']],
                    ['field_type' => $field['field_type'], 'is_required' => $field['is_required'], 'sort_order' => $idx + 1]
                );
            }
        }
    }

    private function saveMeta(Listing $listing, Category $cat, array $fieldValues): void
    {
        foreach ($fieldValues as $label => $value) {
            $field = CustomField::where('category_id', $cat->id)->where('label', $label)->first();
            if ($field) {
                ListingMeta::updateOrCreate(
                    ['listing_id' => $listing->id, 'custom_field_id' => $field->id],
                    ['value' => $value]
                );
            }
        }
    }
}
