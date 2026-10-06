<?php





namespace App\Http\Controllers\Api;





use App\Http\Controllers\Controller;


use App\Models\Category;


use App\Models\CustomField;


use App\Models\Listing;


use App\Models\ListingReview;


use Illuminate\Http\JsonResponse;


use Illuminate\Http\Request;





class ListingController extends Controller


{


    // ─── Helpers ────────────────────────────────────────────────────


    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float


    {


        $R    = 6371;


        $dLat = deg2rad($lat2 - $lat1);


        $dLng = deg2rad($lng2 - $lng1);


        $a    = sin($dLat / 2) ** 2


              + cos(deg2rad($lat1)) * cos(deg2rad($lat2))


              * sin($dLng / 2) ** 2;


        return round($R * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/app-banners


    // Returns global and category-specific banners for the app home


    // ────────────────────────────────────────────────────────────────


    public function appBanners(Request $request)


    {


        $query = \App\Models\Banner::where('is_active', true);


        


        if ($request->filled('category_id')) {


            $query->where(function ($q) use ($request) {


                $q->whereNull('category_id')


                  ->orWhere('category_id', $request->category_id);


            });


        } else {


            $query->whereNull('category_id'); // Only return global banners if no category specified


        }





        $banners = $query->orderBy('sort_order')->get()->map(function ($banner) {


            return [


                'id'         => $banner->id,


                'title'      => $banner->title,


                'image_url'  => asset('storage/' . $banner->image_path),


                'sort_order' => $banner->sort_order,


            ];


        });





        return response()->json([


            'status' => 'success',


            'data'   => $banners


        ]);


    }





    protected function withNearbyScope($query, Request $request)


    {


        // If searching explicitly by a text location, ignore current GPS latitude/longitude.


        if ($request->filled('location')) {


            $query->latest();


            return $query;


        }





        $lat = $request->input('search_latitude', $request->latitude);


        $lng = $request->input('search_longitude', $request->longitude);





        if ($lat && $lng) {


            $lat = (float) $lat;


            $lng = (float) $lng;





            $haversine = "(6371 * acos(cos(radians($lat)) 


                            * cos(radians(lat)) 


                            * cos(radians(lng) - radians($lng)) 


                            + sin(radians($lat)) 


                            * sin(radians(lat))))";





            $sortDir = $request->input('sort_dir', 'asc');





            $query->selectRaw("listings.*, {$haversine} AS distance_km")


                ->whereNotNull('lat')


                ->whereNotNull('lng')


                ->orderBy('distance_km', $sortDir);





            $radius = $request->filled('radius_km') ? (float) $request->radius_km : 100.0;


            $query->whereRaw("{$haversine} <= {$radius}");


        } else {


            $query->latest();


        }





        return $query;


    }





    protected function formatListing($listing)


    {


        if (!$listing) return null;





        $availableRooms = collect();


        if ($listing->relationLoaded('floors')) {


            $availableRooms = $listing->floors->flatMap->rooms->filter(fn ($room) => (int) $room->available_beds > 0)->values();


        }





        if ($listing->relationLoaded('packages')) {


            $listing->setRelation('packages', $listing->packages->values());


        }





        $listing->setAttribute('available_rooms_count', $availableRooms->count());


        $listing->setAttribute('available_beds_count', $availableRooms->sum('available_beds'));


        $listing->setAttribute('fully_booked', $availableRooms->isEmpty());


        $listing->setAttribute('starting_price', $listing->packages_min_price !== null ? (float) $listing->packages_min_price : null);


        $listing->setAttribute('landmark', $listing->landmark);


        $listing->setAttribute('opening_time', $listing->opening_time);


        $listing->setAttribute('closing_time', $listing->closing_time);





        // Ensure distance_km is always explicitly formatted and returned in JSON


        $distance = $listing->getAttribute('distance_km');


        $listing->setAttribute('distance_km', $distance !== null ? round((float) $distance, 2) : null);





        // Map images to include full base URL


        if ($listing->relationLoaded('images')) {


            $listing->setRelation('images', $listing->images->map(fn($img) => [


                'id'         => $img->id,


                'url'        => asset('storage/' . $img->image_path),


                'caption'    => $img->caption,


                'sort_order' => $img->sort_order,


            ])->values());


        }





        return $listing;





    }





    private function getListing(string $id): ?Listing


    {


        return Listing::with(['category', 'partner', 'images', 'meta.customField'])


            ->approved()->find($id);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/categories


    // List all active categories


    // ────────────────────────────────────────────────────────────────


    public function categories()


    {


        $categories = Category::active()->get();


        return response()->json([


            'status' => 'success',


            'data'   => $categories


        ]);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/listings


    // List/Search all approved listings


    // ────────────────────────────────────────────────────────────────


    public function listings(Request $request)


    {


        $query = Listing::with([


            'images',


        ])->withMin('packages', 'price')


          ->withAvg('reviews', 'rating')


          ->withCount('reviews')


          ->approved();





        $this->withNearbyScope($query, $request);





        if ($request->filled('category_id')) {


            $query->where('category_id', $request->category_id);


        }





        if ($request->filled('search')) {


            $search = $request->search;


            $tokens = explode(' ', $search);


            


            // Filter out common stop words to improve search accuracy


            $stopWords = ['in', 'for', 'the', 'at', 'on', 'of', 'and', 'with', 'to', 'a', 'is', 'near', 'by'];


            $tokens = array_filter($tokens, fn($t) => !in_array(strtolower($t), $stopWords) && strlen($t) > 1);





            if (empty($tokens)) {


                $tokens = [$search]; // Fallback if they only typed stop words


            }





            foreach ($tokens as $token) {


                $query->where(function($q) use ($token) {


                    $q->where('title', 'like', "%{$token}%")


                      ->orWhere('city', 'like', "%{$token}%")


                      ->orWhere('state', 'like', "%{$token}%")


                      ->orWhere('address', 'like', "%{$token}%")


                      ->orWhere('landmark', 'like', "%{$token}%")


                      ->orWhere('search_keywords', 'like', "%{$token}%")


                      ->orWhereHas('category', function($catQuery) use ($token) {


                          $catQuery->where('name', 'like', "%{$token}%");


                      });


                });


            }


        }





        if ($request->filled('location')) {


            $location = $request->location;


            $query->where(function($q) use ($location) {


                $q->where('city', 'like', "%{$location}%")


                  ->orWhere('state', 'like', "%{$location}%")


                  ->orWhere('address', 'like', "%{$location}%");


            });


        }





        $listings = $query->paginate(15);


        $listings->getCollection()->transform(fn ($listing) => $this->formatListing($listing));





        return response()->json([


            'status' => 'success',


            'data'   => $listings


        ]);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/listings/{id}/banners


    // Banner image list for the listing home screen


    // ────────────────────────────────────────────────────────────────


    public function banners(string $id): JsonResponse


    {


        $listing = Listing::with('images')->approved()->findOrFail($id);





        $banners = $listing->images->map(fn($img) => [


            'id'        => $img->id,


            'url'       => asset('storage/' . $img->image_path),


            'caption'   => $img->caption,


            'sort_order'=> $img->sort_order,


        ]);





        return response()->json([


            'status' => 'success',


            'data'   => $banners,


        ]);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/listings/{id}/profile


    // Full profile: images, contact, distance, about, meta info


    // ────────────────────────────────────────────────────────────────


    public function profile(Request $request, string $id): JsonResponse


    {


        $listing = Listing::with([


            'category',


            'partner',


            'images',


            'meta.customField',


        ])->withMin('packages', 'price')


          ->withAvg('reviews', 'rating')


          ->withCount('reviews')


          ->approved()->findOrFail($id);





        if ($user = auth('api')->user()) {


            $cacheKey = "profile_visit_{$user->id}_{$listing->id}";


            if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {


                try {


                    if ($listing->partner?->fcm_token) {


                        app(\App\Services\FirebaseNotificationService::class)->sendNotification(


                            $listing->partner->fcm_token,


                            'Listing Viewed',


                            "{$user->name} just checked your listing profile.",


                            ['listing_id' => $listing->id, 'type' => 'listing_view']


                        );


                    }


                } catch (\Exception $e) {


                    \Illuminate\Support\Facades\Log::error('Profile visit notify failed: ' . $e->getMessage());


                }


                \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(1));


            }


        }





        $distanceKm = null;


        if ($request->filled('latitude') && $request->filled('longitude')) {


            $distanceKm = $this->distanceKm(


                (float) $request->latitude,


                (float) $request->longitude,


                (float) $listing->lat,


                (float) $listing->lng


            );


        }





        $avgRating   = $listing->reviews()->avg('rating');


        $reviewCount = $listing->reviews()->count();





        // Star-wise distribution (5 → 1)


        $distribution = [];


        for ($star = 5; $star >= 1; $star--) {


            $count = \App\Models\ListingReview::where('listing_id', $id)->where('rating', $star)->count();


            $percent = $reviewCount > 0 ? round(($count / $reviewCount) * 100, 1) : 0.0;


            $distribution[$star] = ['count' => $count, 'percent' => $percent];


        }





        return response()->json([


            'status' => 'success',


            'data'   => [


                'id'           => $listing->id,


                'title'        => $listing->title,


                'category'     => [


                    'id'           => $listing->category->id,


                    'name'         => $listing->category->name,


                    'icon'         => $listing->category->icon,


                    'has_shifts'   => $listing->category->has_shifts,


                    'has_trainers' => $listing->category->has_trainers,


                    'has_rooms'    => $listing->category->has_rooms,


                ],


                'about'        => $listing->description,


                'address'      => $listing->address,


                'landmark'     => $listing->landmark,


                'opening_time' => $listing->opening_time,


                'closing_time' => $listing->closing_time,


                'phone'        => $listing->phone,


                'lat'          => $listing->lat,


                'lng'          => $listing->lng,


                'distance_km'  => $distanceKm,


                'security_deposit' => $listing->security_deposit,


                'starting_price' => $listing->packages_min_price !== null ? (float) $listing->packages_min_price : null,


                'rating'       => [


                    'average'      => $avgRating ? round($avgRating, 1) : null,


                    'count'        => $reviewCount,


                    'distribution' => $distribution,


                ],


                'images'       => $listing->images->map(fn($img) => [


                    'id'  => $img->id,


                    'url' => asset('storage/' . $img->image_path),


                ]),


                'partner' => [


                    'name'   => $listing->partner->name,
                    'mobile' => $listing->partner->mobile,
                    'profile_photo_url' => filled($listing->partner->profile_photo_url) 
                                            ? $listing->partner->profile_photo_url 
                                            : asset('images/default.png'),


                ],


            ],


        ]);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/listings/{id}/facilities


    // Custom fields as facility list (universal for any category)


    // ────────────────────────────────────────────────────────────────


    public function facilities(string $id): JsonResponse


    {


        $listing = Listing::with(['category', 'meta.customField'])


            ->approved()->findOrFail($id);





        $facilities = $listing->meta


            ->filter(fn($m) => $m->customField && !empty($m->value))


            ->map(fn($m) => [


                'label'      => $m->customField->label,


                'value'      => $m->value,


                'field_type' => $m->customField->field_type,


            ])->values();





        return response()->json([


            'status' => 'success',


            'data'   => $facilities,


        ]);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/listings/{id}/staff


    // Universal staff/trainer list (works for gym, dance, sports, etc.)


    // ────────────────────────────────────────────────────────────────


    public function staff(string $id): JsonResponse


    {


        $listing = Listing::with('trainers')->approved()->findOrFail($id);





        $staff = $listing->trainers->map(fn($t) => [


            'id'               => $t->id,


            'name'             => $t->name,


            'specialization'   => $t->specialization,


            'experience_years' => $t->experience_years,


            'photo_url'        => $t->photo_url,


        ]);





        return response()->json([


            'status' => 'success',


            'data'   => $staff,


        ]);


    }





    public function staffProfile(string $id, string $staffId): JsonResponse


    {


        $listing = Listing::with('trainers')->approved()->findOrFail($id);





        $trainer = $listing->trainers->where('id', $staffId)->first();





        if (!$trainer) {


            return response()->json([


                'status'  => 'error',


                'message' => 'Trainer not found for this listing.',


            ], 404);


        }





        return response()->json([


            'status' => 'success',


            'data'   => [


                'id'               => $trainer->id,


                'name'             => $trainer->name,


                'specialization'   => $trainer->specialization,


                'experience_years' => $trainer->experience_years,


                'bio'              => $trainer->description,


                'photo_url'        => $trainer->photo_url,


            ],


        ]);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/listings/{id}/branches


    // Other listings by same partner under same category


    // ────────────────────────────────────────────────────────────────


    public function branches(string $id): JsonResponse


    {


        $listing = Listing::approved()->findOrFail($id);





        $branches = Listing::with('images')


            ->approved()


            ->where('partner_id', $listing->partner_id)


            ->where('category_id', $listing->category_id)


            ->where('id', '!=', $id)


            ->get()


            ->map(fn($b) => [


                'id'      => $b->id,


                'title'   => $b->title,


                'address' => $b->address,


                'phone'   => $b->phone,


                'image'   => $b->images->first()


                    ? asset('storage/' . $b->images->first()->image_path)


                    : null,


            ]);





        return response()->json([


            'status' => 'success',


            'data'   => $branches,


        ]);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/listings/{id}/reviews


    // Customer reviews with average rating


    // ────────────────────────────────────────────────────────────────


    public function reviews(string $id): JsonResponse


    {


        $listing = Listing::approved()->findOrFail($id);





        $reviews = ListingReview::with('customer')


            ->where('listing_id', $id)


            ->latest()


            ->paginate(15);





        $reviews->getCollection()->transform(fn($r) => [


            'id'           => $r->id,


            'rating'       => $r->rating,


            'comment'      => $r->comment,
            'customer_name' => $r->customer->name ?? 'Anonymous',
            'avatar' => filled($r->customer->profile_photo_url)
                ? $r->customer->profile_photo_url
                : asset('images/default.png'),
            'created_at' => $r->created_at->diffForHumans(),


        ]);





        $avg = ListingReview::where('listing_id', $id)->avg('rating');





        return response()->json([


            'status' => 'success',


            'data'   => [


                'average_rating' => $avg ? round($avg, 1) : null,


                'total_reviews'  => ListingReview::where('listing_id', $id)->count(),


                'reviews'        => $reviews,


            ],


        ]);


    }





    // ────────────────────────────────────────────────────────────────


    // POST /api/listings/{id}/reviews


    // Submit a review (authenticated customer)


    // ────────────────────────────────────────────────────────────────


    public function submitReview(Request $request, string $id): JsonResponse


    {


        $request->validate([


            'rating'  => 'required|integer|min:1|max:5',


            'comment' => 'nullable|string|max:500',


        ]);





        $listing = Listing::approved()->findOrFail($id);





        $review = ListingReview::updateOrCreate(


            ['listing_id' => $listing->id, 'customer_id' => $request->user()->id],


            ['rating' => $request->rating, 'comment' => $request->comment]


        );





        return response()->json([


            'status'  => 'success',


            'message' => 'Review submitted.',


            'data'    => $review,


        ]);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/listings/{id}/durations


    // Available plan duration types for "Choose Your Plan" screen


    // ────────────────────────────────────────────────────────────────


    public function durations(string $id): JsonResponse


    {


        $listing = Listing::approved()->findOrFail($id);





        $types = $listing->packages()


            ->whereNull('room_id')           // non-room packages for activity listings


            ->orWhereNotNull('room_id')      // include room packages


            ->select('type')


            ->distinct()


            ->pluck('type');





        $labels = [


            'monthly'     => ['label' => 'Monthly',         'duration_days' => 30],


            'quarterly'   => ['label' => 'Quarterly',       'duration_days' => 90],


            'half_yearly' => ['label' => 'Half Yearly',     'duration_days' => 180],


            'yearly'      => ['label' => 'Yearly',          'duration_days' => 365],


            'custom'      => ['label' => 'Custom',          'duration_days' => null],


        ];





        $durations = $types->map(fn($type) => array_merge(


            ['type' => $type],


            $labels[$type] ?? ['label' => ucfirst($type), 'duration_days' => null]


        ))->values();





        return response()->json([


            'status' => 'success',


            'data'   => $durations,


        ]);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/listings/{id}/shifts


    // Batch/shift times for the listing (Gym, Dance, etc.)


    // ────────────────────────────────────────────────────────────────


    public function shifts(string $id): JsonResponse


    {


        $listing = Listing::with('shifts')->approved()->findOrFail($id);





        $shifts = $listing->shifts->map(fn($s) => [


            'id'          => $s->id,


            'name'        => $s->shift_label,


            'start_time'  => $s->start_time,


            'end_time'    => $s->end_time,


            'max_members' => $s->max_members,


            'fee'         => (float) $s->fee,


        ]);





        return response()->json([


            'status' => 'success',


            'data'   => $shifts,


        ]);


    }





    // ────────────────────────────────────────────────────────────────


    // GET /api/listings/{id}/plans?type=monthly


    // Plan list filtered by duration type + batch/shift times


    // ────────────────────────────────────────────────────────────────


    public function plans(Request $request, string $id): JsonResponse


    {


        $listing = Listing::with(['category', 'shifts', 'packages.room'])


            ->approved()->findOrFail($id);





        $query = $listing->packages()->with('room');





        if ($request->filled('type')) {


            $query->where('type', $request->type);


        }





        $allPackages = $query->get();

        $formatPackage = fn($pkg) => [
            'id'              => $pkg->id,
            'name'            => $pkg->name,
            'type'            => $pkg->type,
            'duration_label'  => $pkg->duration_label,
            'duration_days'   => $pkg->duration_days,
            'price'           => (float) $pkg->price,
            'features'        => $pkg->features ?? [],
            'room_type'       => $pkg->room_type,
            'room_id'         => $pkg->room_id,
            'occupancy_type'  => $pkg->occupancy_type,
            'meal_plans'      => $pkg->meal_plans,
        ];

        $globalPackages = $allPackages->whereNull('room_id')->values();

        $available_rooms = collect();
        $packages = $allPackages->map($formatPackage);

        if ($listing->category->has_rooms) {
            $listing->load('floors.rooms');

            $roomsQuery = $listing->floors->flatMap->rooms->filter(fn($r) => $r->available_beds > 0);
            
            if ($request->filled('floor_id')) {
                $roomsQuery = $roomsQuery->where('floor_id', $request->floor_id);
            }
            if ($request->filled('room_id')) {
                $roomsQuery = $roomsQuery->where('id', $request->room_id);
            }

            $available_rooms = $roomsQuery->map(function($r) use ($allPackages, $globalPackages, $formatPackage) {
                $roomPackages = $allPackages->where('room_id', $r->id)->values();
                $applicablePackages = $globalPackages->merge($roomPackages)->map($formatPackage)->sortBy('price')->values();
                $minPrice = $applicablePackages->min('price') ?? 0;

                return [
                    'id' => $r->id,
                    'floor_id' => $r->floor_id,
                    'room_number' => $r->room_number,
                    'room_type' => $r->room_type,
                    'capacity' => $r->capacity,
                    'available_beds' => $r->available_beds,
                    'security_deposit' => (float) $r->security_deposit,
                    'starting_price' => $minPrice,
                    'packages' => $applicablePackages,
                ];
            })->sortBy('starting_price')->values();

            // Filter top-level plans array based on available rooms
            $roomIds = $available_rooms->pluck('id')->toArray();
            $filteredPackages = $allPackages->filter(function($pkg) use ($roomIds) {
                return is_null($pkg->room_id) || in_array($pkg->room_id, $roomIds);
            });
            $packages = $filteredPackages->map($formatPackage)->values();
        }





        // Batch / shift times (for activity listings with shifts)


        $shifts = $listing->shifts->map(fn($s) => [


            'id'          => $s->id,


            'name'        => $s->shift_label,


            'start_time'  => $s->start_time,


            'end_time'    => $s->end_time,


            'max_members' => $s->max_members,


            'fee'         => (float) $s->fee,


        ]);





        return response()->json([


            'status' => 'success',


            'data'   => [


                'plans'        => $packages,


                'batch_times'  => $shifts,   // empty array if no shifts
                'rooms'        => $available_rooms,
                'has_shifts'   => $listing->category->has_shifts,


                'has_rooms'    => $listing->category->has_rooms,


            ],


        ]);


    }

    public function floors(Request $request, string $id): JsonResponse
    {
        $listing = Listing::with('category', 'floors')->approved()->findOrFail($id);

        if (!$listing->category->has_rooms) {
            return response()->json([
                'status' => 'success',
                'data'   => []
            ]);
        }

        $floors = $listing->floors->map(fn($f) => [
            'id' => $f->id,
            'floor_number' => $f->floor_number,
            'name' => $f->name,
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $floors,
        ]);
    }

    public function rooms(Request $request, string $id): JsonResponse
    {
        $listing = Listing::with('category', 'floors.rooms.images')->approved()->findOrFail($id);

        if (!$listing->category->has_rooms) {
            return response()->json([
                'status' => 'success',
                'data'   => []
            ]);
        }

        $roomsQuery = $listing->floors->flatMap->rooms->filter(fn($r) => $r->available_beds > 0);
        
        if ($request->filled('floor_id')) {
            $roomsQuery = $roomsQuery->where('floor_id', $request->floor_id);
        }

        if ($request->filled('room_id')) {
            $roomsQuery = $roomsQuery->where('id', $request->room_id);
        }

        $allPackages = \App\Models\Package::where('listing_id', $id)->get();
        $globalPackages = $allPackages->whereNull('room_id')->values();

        $formatPackage = fn($pkg) => [
            'id'              => $pkg->id,
            'name'            => $pkg->name,
            'type'            => $pkg->type,
            'duration_label'  => $pkg->duration_label,
            'duration_days'   => $pkg->duration_days,
            'price'           => (float) $pkg->price,
            'features'        => $pkg->features ?? [],
            'room_type'       => $pkg->room_type,
            'room_id'         => $pkg->room_id,
            'occupancy_type'  => $pkg->occupancy_type,
            'meal_plans'      => $pkg->meal_plans,
        ];

        $rooms = $roomsQuery->map(function($r) use ($allPackages, $globalPackages, $formatPackage) {
            $roomPackages = $allPackages->where('room_id', $r->id)->values();
            $applicablePackages = $globalPackages->merge($roomPackages)->map($formatPackage)->sortBy('price')->values();
            $minPrice = $applicablePackages->min('price') ?? 0;

            return [
                'id' => $r->id,
                'floor_id' => $r->floor_id,
                'room_number' => $r->room_number,
                'room_type' => $r->room_type,
                'capacity' => $r->capacity,
                'available_beds' => $r->available_beds,
                'security_deposit' => (float) $r->security_deposit,
                'starting_price' => $minPrice,
                'packages' => $applicablePackages,
                'images' => $r->images->map(fn($img) => ['id' => $img->id, 'url' => $img->url])->values(),
            ];
        })->sortBy('starting_price')->values();

        return response()->json([
            'status' => 'success',
            'data'   => $rooms,
        ]);
    }

    public function roomDetails(Request $request, string $id, string $roomId): JsonResponse
    {
        $listing = Listing::with('category', 'partner', 'reviews')->approved()->findOrFail($id);

        if (!$listing->category->has_rooms) {
            return response()->json(['status' => 'error', 'message' => 'Listing does not have rooms'], 400);
        }

        $room = \App\Models\Room::with('images')->whereHas('floor', function($q) use ($id) {
            $q->where('listing_id', $id);
        })->findOrFail($roomId);

        $allPackages = \App\Models\Package::where('listing_id', $id)->get();
        $globalPackages = $allPackages->whereNull('room_id')->values();
        $roomPackages = $allPackages->where('room_id', $room->id)->values();

        $formatPackage = fn($pkg) => [
            'id'              => $pkg->id,
            'name'            => $pkg->name,
            'type'            => $pkg->type,
            'duration_label'  => $pkg->duration_label,
            'duration_days'   => $pkg->duration_days,
            'price'           => (float) $pkg->price,
            'features'        => $pkg->features ?? [],
            'room_type'       => $pkg->room_type,
            'room_id'         => $pkg->room_id,
            'occupancy_type'  => $pkg->occupancy_type,
            'meal_plans'      => $pkg->meal_plans,
        ];

        $applicablePackages = $globalPackages->merge($roomPackages)->map($formatPackage)->sortBy('price')->values();
        $minPrice = $applicablePackages->min('price') ?? 0;

        $distanceKm = null;
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $distanceKm = $this->distanceKm(
                (float) $request->latitude, (float) $request->longitude,
                (float) $listing->lat, (float) $listing->lng
            );
        }

        $avgRating   = $listing->reviews()->avg('rating');
        $reviewCount = $listing->reviews()->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id' => $room->id,
                'floor_id' => $room->floor_id,
                'room_number' => $room->room_number,
                'room_type' => $room->room_type,
                'capacity' => $room->capacity,
                'available_beds' => $room->available_beds,
                'security_deposit' => (float) $room->security_deposit,
                'starting_price' => $minPrice,
                'images' => $room->images->map(fn($img) => ['id' => $img->id, 'url' => $img->url])->values(),
                'packages' => $applicablePackages,
                'listing' => [
                    'id'           => $listing->id,
                    'title'        => $listing->title,
                    'about'        => $listing->description,
                    'address'      => $listing->address,
                    'landmark'     => $listing->landmark,
                    'phone'        => $listing->phone,
                    'lat'          => $listing->lat,
                    'lng'          => $listing->lng,
                    'distance_km'  => $distanceKm,
                    'rating'       => [
                        'average'      => $avgRating ? round($avgRating, 1) : null,
                        'count'        => $reviewCount,
                    ],
                    'category'     => [
                        'id'           => $listing->category->id,
                        'name'         => $listing->category->name,
                    ],
                ],
                'partner' => [
                    'name'   => $listing->partner->name,
                    'mobile' => $listing->partner->mobile,
                    'profile_photo_url' => filled($listing->partner->profile_photo_url) 
                                            ? $listing->partner->profile_photo_url 
                                            : asset('images/default.png'),
                ],
            ],
        ]);
    }
}


