<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Floor;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\ListingMeta;
use App\Models\ListingShift;
use App\Models\ListingTrainer;
use App\Models\Package;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Carbon\Carbon;

class ListingController extends Controller
{
    private function resizeAndStoreImage($img, string $folder, int $width, int $height): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($img->getRealPath());
        $image->cover($width, $height);
        
        $ext = strtolower($img->getClientOriginalExtension());
        if ($ext === 'png') {
            $encoded = $image->toPng();
        } elseif ($ext === 'webp') {
            $encoded = $image->toWebp();
        } elseif ($ext === 'gif') {
            $encoded = $image->toGif();
        } else {
            $encoded = $image->toJpeg(90);
            $ext = 'jpg';
        }
        
        $filename = uniqid() . '.' . $ext;
        $path = $folder . '/' . $filename;
        Storage::disk('public')->put($path, (string) $encoded);
        
        return $path;
    }

    private function markAsPendingIfEdited(Listing $listing): void
    {
        if (in_array($listing->status, ['approved', 'rejected'])) {
            $listing->update(['status' => 'pending']);
        }
    }

    private function getListing(string $id)
    {
        return Listing::where('partner_id', auth('partner_api')->id())->findOrFail($id);
    }

    /**
     * Get all listings for the authenticated partner
     */
    public function index()
    {
        $listings = Listing::with(['category', 'images'])
            ->where('partner_id', auth('partner_api')->id())
            ->latest()
            ->get();
            
        return response()->json(['status' => 'success', 'data' => $listings]);
    }

    /**
     * Create a basic draft listing
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'gender_type'  => 'nullable|in:boys,girls,co-living',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'address'      => 'required|string',
            'city'         => 'nullable|string',
            'state'        => 'nullable|string',
            'pincode'      => 'nullable|string',
            'landmark'     => 'nullable|string|max:255',
            'lat'          => 'required|numeric|between:-90,90',
            'lng'          => 'required|numeric|between:-180,180',
        ]);

        $catName = Category::find($data['category_id'])?->name ?? '';
        $data['search_keywords'] = implode(', ', array_filter([$data['title'], $catName, $data['city'] ?? '', $data['state'] ?? '', $data['landmark'] ?? '']));
        
        $data['partner_id'] = auth('partner_api')->id();
        $data['created_by'] = auth('partner_api')->id();
        $data['status'] = 'draft';

        $listing = Listing::create($data);

        return response()->json([
            'status' => 'success', 
            'message' => 'Listing draft created successfully.',
            'data' => $listing
        ], 201);
    }

    /**
     * Get details of a specific listing
     */
    public function show($id)
    {
        $listing = Listing::with([
            'images', 'meta', 'category', 'packages.room', 'shifts', 'trainers', 'floors.rooms.images'
        ])->where('partner_id', auth('partner_api')->id())->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $listing]);
    }

    /**
     * Update basic listing info
     */
    public function update(Request $request, $id)
    {
        $listing = $this->getListing($id);

        if (!in_array($listing->status, ['draft', 'approved', 'rejected', 'pending'])) {
            return response()->json(['status' => 'error', 'message' => 'Cannot edit a listing in ' . $listing->status . ' status.'], 400);
        }

        $data = $request->validate([
            'category_id'  => 'sometimes|required|exists:categories,id',
            'gender_type'  => 'nullable|in:boys,girls,co-living',
            'title'        => 'sometimes|required|string|max:255',
            'description'  => 'nullable|string',
            'address'      => 'sometimes|required|string',
            'city'         => 'nullable|string',
            'state'        => 'nullable|string',
            'pincode'      => 'nullable|string',
            'landmark'     => 'nullable|string|max:255',
            'lat'          => 'sometimes|required|numeric|between:-90,90',
            'lng'          => 'sometimes|required|numeric|between:-180,180',
        ]);

        $listing->update($data);
        $this->markAsPendingIfEdited($listing);

        return response()->json([
            'status' => 'success',
            'message' => 'Listing updated successfully.',
            'data' => $listing->fresh()
        ]);
    }

    /**
     * Delete a listing entirely
     */
    public function destroy($id)
    {
        $listing = $this->getListing($id);
        
        foreach ($listing->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }
        
        $listing->delete();

        return response()->json(['status' => 'success', 'message' => 'Listing deleted successfully.']);
    }

    // ==========================================
    // Sub-Resources Methods
    // ==========================================

    /**
     * Upload Listing Images
     */
    public function uploadImages(Request $request, $id)
    {
        $listing = $this->getListing($id);

        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'image|max:2048'
        ]);

        $uploaded = [];
        foreach ($request->file('images') as $img) {
            $path = $this->resizeAndStoreImage($img, 'listings', 800, 600);
            $listingImg = ListingImage::create([
                'listing_id' => $listing->id,
                'image_path' => $path,
                'sort_order' => $listing->images()->count(),
            ]);
            $uploaded[] = $listingImg;
        }

        $this->markAsPendingIfEdited($listing);

        return response()->json([
            'status' => 'success',
            'message' => count($uploaded) . ' images uploaded successfully.',
            'data' => $uploaded
        ]);
    }

    public function deleteImage($id, $imageId)
    {
        $listing = $this->getListing($id);
        $img = ListingImage::where('listing_id', $listing->id)->findOrFail($imageId);
        
        Storage::disk('public')->delete($img->image_path);
        $img->delete();
        $this->markAsPendingIfEdited($listing);

        return response()->json(['status' => 'success', 'message' => 'Image deleted successfully.']);
    }

    /**
     * Update Meta/Custom Fields
     */
    public function updateMeta(Request $request, $id)
    {
        $listing = $this->getListing($id);
        
        $request->validate([
            'meta' => 'required|array',
            'meta.*' => 'nullable|string'
        ]);

        foreach ($request->input('meta') as $fieldId => $value) {
            ListingMeta::updateOrCreate(
                ['listing_id' => $listing->id, 'custom_field_id' => $fieldId],
                ['value' => $value]
            );
        }

        $this->markAsPendingIfEdited($listing);

        return response()->json(['status' => 'success', 'message' => 'Custom fields updated successfully.']);
    }

    /**
     * Add Shift
     */
    public function addShift(Request $request, $id)
    {
        $listing = $this->getListing($id);

        $data = $request->validate([
            'shift_name'  => 'required|in:morning,evening,night',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
            'max_members' => 'required|integer|min:1',
            'fee'         => 'required|numeric|min:0',
        ]);

        $data['listing_id'] = $listing->id;
        $shift = ListingShift::create($data);
        
        $this->markAsPendingIfEdited($listing);

        return response()->json(['status' => 'success', 'message' => 'Shift added successfully.', 'data' => $shift]);
    }

    public function deleteShift($id, $shiftId)
    {
        $listing = $this->getListing($id);
        ListingShift::where('listing_id', $listing->id)->findOrFail($shiftId)->delete();
        $this->markAsPendingIfEdited($listing);
        return response()->json(['status' => 'success', 'message' => 'Shift deleted successfully.']);
    }

    /**
     * Add Trainer
     */
    public function addTrainer(Request $request, $id)
    {
        $listing = $this->getListing($id);

        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'specialization'   => 'nullable|string|max:100',
            'description'      => 'nullable|string|max:500',
            'experience_years' => 'required|integer|min:0',
            'photos'           => 'required|array|min:3',
            'photos.*'         => 'image|max:1024',
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photos[] = $this->resizeAndStoreImage($photo, 'trainers', 600, 600);
            }
        }

        $trainer = ListingTrainer::create([
            'listing_id'       => $listing->id,
            'name'             => $data['name'],
            'specialization'   => $data['specialization'] ?? null,
            'description'      => $data['description'] ?? null,
            'experience_years' => $data['experience_years'],
            'photo'            => json_encode($photos),
        ]);

        $this->markAsPendingIfEdited($listing);

        return response()->json(['status' => 'success', 'message' => 'Trainer added successfully.', 'data' => $trainer]);
    }

    public function deleteTrainer($id, $trainerId)
    {
        $listing = $this->getListing($id);
        $trainer = ListingTrainer::where('listing_id', $listing->id)->findOrFail($trainerId);
        
        if ($trainer->photo) {
            $photos = json_decode($trainer->photo, true);
            if (is_array($photos)) {
                foreach ($photos as $p) {
                    if ($p) Storage::disk('public')->delete($p);
                }
            } else {
                Storage::disk('public')->delete($trainer->photo);
            }
        }
        $trainer->delete();
        $this->markAsPendingIfEdited($listing);

        return response()->json(['status' => 'success', 'message' => 'Trainer deleted successfully.']);
    }

    /**
     * Add Floor
     */
    public function addFloor(Request $request, $id)
    {
        $listing = $this->getListing($id);
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'floor_number' => 'required|integer',
        ]);
        
        $data['listing_id'] = $listing->id;
        $floor = Floor::create($data);
        $this->markAsPendingIfEdited($listing);

        return response()->json(['status' => 'success', 'message' => 'Floor added successfully.', 'data' => $floor]);
    }

    public function deleteFloor($id, $floorId)
    {
        $listing = $this->getListing($id);
        Floor::where('listing_id', $listing->id)->findOrFail($floorId)->delete();
        $this->markAsPendingIfEdited($listing);
        return response()->json(['status' => 'success', 'message' => 'Floor deleted successfully.']);
    }

    /**
     * Add Room
     */
    public function addRoom(Request $request, $id)
    {
        $listing = $this->getListing($id);
        
        $data = $request->validate([
            'floor_id'         => 'required|exists:floors,id',
            'room_number'      => 'required|string|max:20',
            'room_type'        => 'required|string',
            'capacity'         => 'required|integer|min:1',
            'security_deposit' => 'nullable|numeric|min:0',
            'images'           => 'required|array|min:3',
            'images.*'         => 'image|max:2048',
        ]);

        $room = Room::create([
            'floor_id'         => $data['floor_id'],
            'room_number'      => $data['room_number'],
            'room_type'        => $data['room_type'],
            'capacity'         => $data['capacity'],
            'available_beds'   => $data['capacity'],
            'security_deposit' => $data['security_deposit'] ?? 0,
        ]);

        foreach ($request->file('images') as $img) {
            $path = $this->resizeAndStoreImage($img, 'rooms', 800, 600);
            RoomImage::create([
                'room_id' => $room->id,
                'image_path' => $path,
                'sort_order' => $room->images()->count(),
            ]);
        }

        $this->markAsPendingIfEdited($listing);

        return response()->json(['status' => 'success', 'message' => 'Room added successfully.', 'data' => $room]);
    }

    public function deleteRoom($id, $roomId)
    {
        $listing = $this->getListing($id);
        $room = Room::whereHas('floor', function($q) use ($listing) {
            $q->where('listing_id', $listing->id);
        })->findOrFail($roomId);
        
        $room->delete();
        $this->markAsPendingIfEdited($listing);
        return response()->json(['status' => 'success', 'message' => 'Room deleted successfully.']);
    }

    /**
     * Add Package
     */
    public function addPackage(Request $request, $id)
    {
        $listing = $this->getListing($id);
        
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'duration_days'  => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0',
            'type'           => 'required|in:monthly,quarterly,half_yearly,yearly,custom',
            'occupancy_type' => 'required|in:single,full_room,per_bed,standard,per_site',
            'room_type'      => 'nullable|string',
            'features'       => 'nullable|array',
            'features.*'     => 'string'
        ]);

        $data['listing_id'] = $listing->id;
        $package = Package::create($data);
        $this->markAsPendingIfEdited($listing);

        return response()->json(['status' => 'success', 'message' => 'Package added successfully.', 'data' => $package]);
    }

    public function deletePackage($id, $packageId)
    {
        $listing = $this->getListing($id);
        Package::where('listing_id', $listing->id)->findOrFail($packageId)->delete();
        $this->markAsPendingIfEdited($listing);
        return response()->json(['status' => 'success', 'message' => 'Package deleted successfully.']);
    }

    /**
     * Submit Listing for Review
     */
    public function submit(Request $request, $id)
    {
        $listing = $this->getListing($id);

        if ($listing->images()->count() < 3) {
            return response()->json(['status' => 'error', 'message' => 'You must upload at least 3 property images before submitting.'], 400);
        }

        if ($listing->category && $listing->category->has_trainers) {
            foreach ($listing->trainers as $trainer) {
                $photos = json_decode($trainer->photo, true) ?: [];
                if (count(array_filter($photos)) < 3) {
                    return response()->json(['status' => 'error', 'message' => "Trainer {$trainer->name} must have at least 3 photos."], 400);
                }
            }
        }

        if ($listing->category && $listing->category->has_rooms) {
            foreach ($listing->floors as $floor) {
                foreach ($floor->rooms as $room) {
                    if ($room->images()->count() < 3) {
                        return response()->json(['status' => 'error', 'message' => "Room {$room->room_number} must have at least 3 photos."], 400);
                    }
                }
            }
        }

        $listing->update(['status' => 'pending']);

        return response()->json(['status' => 'success', 'message' => 'Listing submitted for review successfully.']);
    }
}
