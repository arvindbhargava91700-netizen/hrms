<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\Request;

class BrowseController extends Controller
{
    protected function withNearbyScope($query, Request $request)
    {
        $lat = $request->latitude;
        $lng = $request->longitude;

        if ($lat && $lng) {
            $haversine = "(6371 * acos(cos(radians($lat)) 
                            * cos(radians(lat)) 
                            * cos(radians(lng) - radians($lng)) 
                            + sin(radians($lat)) 
                            * sin(radians(lat))))";

            $query->selectRaw("listings.*, {$haversine} AS distance_km")
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->orderBy('distance_km');

            if ($request->filled('radius_km')) {
                $query->having('distance_km', '<=', (float) $request->radius_km);
            }
        } else {
            $query->latest();
        }

        return $query;
    }

    protected function formatListing($listing)
    {
        if (!$listing) {
            return null;
        }

        $availableRooms = collect();
        if ($listing->relationLoaded('floors')) {
            $availableRooms = $listing->floors->flatMap->rooms->filter(fn ($room) => (int) $room->available_beds > 0)->values();
        }

        if ($listing->relationLoaded('packages')) {
            $listing->setRelation('packages', $listing->packages->filter(function ($package) {
                return !$package->room || (int) optional($package->room)->available_beds > 0;
            })->values());
        }

        $listing->setAttribute('available_rooms_count', $availableRooms->count());
        $listing->setAttribute('available_beds_count', $availableRooms->sum('available_beds'));
        $listing->setAttribute('fully_booked', $availableRooms->isEmpty());

        return $listing;
    }

    public function categories()
    {
        $categories = Category::active()->get();

        return response()->json([
            'status' => 'success',
            'data'   => $categories
        ]);
    }

    public function listings(Request $request)
    {
        $query = Listing::with([
            'category',
            'packages.room',
            'images',
            'floors.rooms' => fn ($roomQuery) => $roomQuery
                ->where('available_beds', '>', 0)
                ->with('images')
        ])->withAvg('reviews', 'rating')
          ->withCount('reviews')
          ->approved();

        $this->withNearbyScope($query, $request);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $listings = $query->paginate(15);
        $listings->getCollection()->transform(fn ($listing) => $this->formatListing($listing));

        return response()->json([
            'status' => 'success',
            'data'   => $listings
        ]);
    }

    public function listingDetails($id)
    {
        $listing = Listing::with([
            'category',
            'packages.room',
            'images',
            'floors.rooms' => fn ($roomQuery) => $roomQuery
                ->where('available_beds', '>', 0)
                ->with('images')
        ])->withAvg('reviews', 'rating')
          ->withCount('reviews')
          ->approved()->find($id);

        $listing = $this->formatListing($listing);

        return response()->json([
            'status' => 'success',
            'data'   => $listing
        ]);
    }
}
