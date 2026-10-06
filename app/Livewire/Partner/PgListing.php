<?php



namespace App\Livewire\Partner;



use App\Models\Category;

use App\Models\CustomField;

use App\Models\Floor;

use App\Models\Listing;

use App\Models\ListingImage;

use App\Models\ListingMeta;

use App\Models\Package;

use App\Models\RoomImage;

use App\Models\Room;

use Illuminate\Support\Facades\Storage;

use Livewire\Attributes\Locked;
use Livewire\Component;

use Livewire\WithFileUploads;



class PgListing extends Component

{

    use WithFileUploads;
    use HasPartnerWorkspaceScope;



    #[Locked]
    public ?string $listingId = null;

    public string  $activeTab = 'property';



    // Property Info

    public string $title       = '';

    public string $description = '';

    public string $address     = '';

    public string $latitude    = '';

    public string $longitude   = '';

    public $newImages = [];



    // Floor Form

    public string $floorName   = '';

    public int    $floorNumber = 0;



    // Room Form (tied to a floor)

    public ?int   $roomFloorId     = null;

    public string $roomNumber      = '';

    public string $roomType        = 'single';

    public int    $capacity        = 1;

    public float  $securityDeposit = 0;

    public $roomImages = [];



    // Package Form

    public ?string $packageRoomId = null;
    public ?int $packageFloorId = null;
    public ?string $editingPackageId = null;

    public string $packageName = '';

    public int $packageDurationDays = 30;

    public string $packagePrice = '';

    public string $packageType = 'monthly';

    public string $packageOccupancyType = 'per_bed';

    public string $packageFeaturesInput = '';
    
    // Meal Plans
    public bool $packageHasBreakfast = false;
    public bool $packageHasLunch = false;
    public bool $packageHasDinner = false;
    public string $packageMealType = 'none';



    // Custom Fields

    public array $metaValues = [];



    public function mount(?string $id = null): void

    {
        $this->selectedWorkspacePartnerId();
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess($id ? 'listing_update' : 'listing_create'), 403);

        $this->listingId = $id;

        if ($id) {

            $listing = $this->scopePartnerRecords(Listing::with(['meta']))->findOrFail($id);

            $this->title       = $listing->title;

            $this->description = $listing->description ?? '';

            $this->address     = $listing->address ?? '';

            $this->latitude    = (string) ($listing->lat ?? '');

            $this->longitude   = (string) ($listing->lng ?? '');

            foreach ($listing->meta as $m) {

                $this->metaValues[$m->custom_field_id] = $m->value;

            }

        }

    }



    public function saveProperty(): void

    {

        $this->validate([

            'title'       => 'required|string|max:255',

            'description' => 'nullable|string',

            'address'     => 'required|string',

            'latitude'    => 'required|numeric|between:-90,90',

            'longitude'   => 'required|numeric|between:-180,180',

            'newImages.*' => 'nullable|image|max:2048',

        ]);



        $pgCategory = Category::whereIn('name', ['PG / Hostel', 'Room Rental'])->first();



        if ($this->listingId) {

            $listing = $this->scopePartnerRecords(Listing::query())->findOrFail($this->listingId);

            if (empty($listing->created_by)) {
                $listing->created_by = auth()->id();
                $listing->save();
            }

            $listing->update([

                'title'       => $this->title,

                'description' => $this->description,

                'address'     => $this->address,

            ]);

        } else {

            $listing = Listing::create([

                'partner_id'  => $this->requirePartnerIdForWrite(),
                'created_by'  => auth()->id(),

                'category_id' => $pgCategory->id,

                'title'       => $this->title,

                'description' => $this->description,

                'address'     => $this->address,

                'lat'         => $this->latitude,

                'lng'         => $this->longitude,

                'status'      => 'pending',

            ]);

            $this->listingId = $listing->id;

        }



        foreach ($this->newImages as $img) {

            $path = $img->store('listings', 'public');

            ListingImage::create([

                'listing_id' => $listing->id,

                'image_path' => $path,

                'sort_order' => $listing->images()->count(),

            ]);

        }



        $this->newImages = [];

        $this->activeTab = 'floors';

        session()->flash('success', 'Property info saved! Now add floors.');

    }



    public function deleteImage(int $id): void

    {

        $img = ListingImage::whereHas('listing', fn ($query) => $this->scopePartnerRecords($query))->findOrFail($id);

        Storage::disk('public')->delete($img->image_path);

        $img->delete();

    }



    public function addFloor(): void

    {

        $this->validate([

            'listingId'   => 'required',

            'floorName'   => 'required|string|max:100',

            'floorNumber' => 'required|integer',

        ]);



        Floor::create([

            'listing_id'   => $this->listingId,

            'name'         => $this->floorName,

            'floor_number' => $this->floorNumber,

        ]);



        $this->reset(['floorName', 'floorNumber']);

        session()->flash('success', 'Floor added!');

    }



    public function deleteFloor(int $id): void

    {

        Floor::where('listing_id', $this->listingId)->findOrFail($id)->delete();

    }



    public function selectFloor(int $floorId): void

    {

        $this->roomFloorId = $floorId;

    }



    public function addRoom(): void

    {

        $this->validate([

            'roomFloorId'     => 'required|exists:floors,id',

            'roomNumber'      => 'required|string|max:20',

            'capacity'        => 'required|integer|min:1',

            'roomImages.*'    => 'nullable|image|max:2048',

            'securityDeposit' => 'nullable|numeric|min:0',

        ]);



        $room = Room::create([

            'floor_id'         => $this->roomFloorId,

            'room_number'      => $this->roomNumber,

            'room_type'        => $this->roomType,

            'capacity'         => $this->capacity,

            'available_beds'   => $this->capacity,

            'security_deposit' => $this->securityDeposit,

        ]);



        foreach ($this->roomImages as $img) {

            $path = $img->store('rooms', 'public');

            RoomImage::create([

                'room_id' => $room->id,

                'image_path' => $path,

                'sort_order' => $room->images()->count(),

            ]);

        }



        $this->reset(['roomNumber', 'roomType', 'capacity', 'securityDeposit', 'roomImages']);

        $this->roomType = 'single';

        $this->capacity = 1;

        session()->flash('success', 'Room added!');

    }



    public function deleteRoom(string $id): void

    {

        Room::whereHas('floor.listing', fn ($query) => $this->scopePartnerRecords($query))->findOrFail($id)->delete();

    }



    public function saveCustomFields(): void

    {

        foreach ($this->metaValues as $fieldId => $value) {

            ListingMeta::updateOrCreate(

                ['listing_id' => $this->listingId, 'custom_field_id' => $fieldId],

                ['value' => $value]

            );

        }



        session()->flash('success', 'Listing submitted for review!');

        $this->redirect(route('partner.listings'));

    }



    public function savePackage(): void

    {

        $this->validate([

            'listingId' => 'required',

            'packageName' => 'required|string|max:100',

            'packageDurationDays' => 'required|integer|min:1',

            'packagePrice' => 'required|numeric|min:0',

            'packageType' => 'required|in:monthly,quarterly,half_yearly,yearly,custom',

            'packageOccupancyType' => 'required|in:single,full_room,per_bed,standard',

            'packageFeaturesInput' => 'nullable|string',

            'packageRoomId' => 'nullable|exists:rooms,id',

            'packageHasBreakfast' => 'boolean',
            'packageHasLunch' => 'boolean',
            'packageHasDinner' => 'boolean',
            'packageMealType' => 'required|in:veg,non_veg,both,none',
        ]);



        $listing = $this->scopePartnerRecords(Listing::query())->findOrFail($this->listingId);

        $features = array_filter(array_map('trim', explode("\n", $this->packageFeaturesInput)));



        $roomId = null;

        if ($this->packageRoomId) {

            $room = Room::whereHas('floor', fn($q) => $q->where('listing_id', $listing->id))->findOrFail($this->packageRoomId);

            $roomId = $room->id;

        }



        $packageData = [
            'listing_id' => $listing->id,
            'room_id' => $roomId,
            'name' => $this->packageName,
            'occupancy_type' => $this->packageRoomId ? $this->packageOccupancyType : 'standard',
            'duration_days' => $this->packageDurationDays,
            'price' => $this->packagePrice,
            'type' => $this->packageType,
            'features' => empty($features) ? null : $features,
            'meal_plans' => [
                'has_breakfast' => $this->packageHasBreakfast,
                'has_lunch' => $this->packageHasLunch,
                'has_dinner' => $this->packageHasDinner,
                'meal_type' => $this->packageMealType,
            ],
        ];

        if ($this->editingPackageId) {
            $package = Package::whereHas('listing', fn ($query) => $this->scopePartnerRecords($query))
                ->where('listing_id', $this->listingId)->findOrFail($this->editingPackageId);
            $package->update($packageData);
            session()->flash('success', 'Package updated successfully.');
        } else {
            Package::create($packageData);
            session()->flash('success', 'Package added. You can add more or continue.');
        }

        $this->cancelEditPackage();

    }

    public function updatedPackageType($value): void
    {
        switch ($value) {
            case 'monthly':
                $this->packageDurationDays = 30;
                break;
            case 'quarterly':
                $this->packageDurationDays = 90;
                break;
            case 'half_yearly':
                $this->packageDurationDays = 180;
                break;
            case 'yearly':
                $this->packageDurationDays = 365;
                break;
        }
    }

    public function editPackage(string $id): void
    {
        $package = Package::whereHas('listing', fn ($query) => $this->scopePartnerRecords($query))
            ->where('listing_id', $this->listingId)->findOrFail($id);
        
        $this->editingPackageId = $package->id;
        $this->packageName = $package->name;
        $this->packageDurationDays = $package->duration_days;
        $this->packagePrice = $package->price;
        $this->packageType = $package->type;
        $this->packageOccupancyType = $package->occupancy_type ?? 'per_bed';
        
        if ($package->room) {
            $this->packageRoomId = $package->room_id;
            $this->packageFloorId = $package->room->floor_id;
        } else {
            $this->packageRoomId = null;
            $this->packageFloorId = null;
        }

        if (is_array($package->features)) {
            $this->packageFeaturesInput = implode("\n", $package->features);
        } elseif (is_string($package->features)) {
            $decoded = json_decode($package->features, true);
            $this->packageFeaturesInput = is_array($decoded) ? implode("\n", $decoded) : $package->features;
        } else {
            $this->packageFeaturesInput = '';
        }

        $this->packageHasBreakfast = $package->meal_plans['has_breakfast'] ?? false;
        $this->packageHasLunch = $package->meal_plans['has_lunch'] ?? false;
        $this->packageHasDinner = $package->meal_plans['has_dinner'] ?? false;
        $this->packageMealType = $package->meal_plans['meal_type'] ?? 'none';
    }

    public function cancelEditPackage(): void
    {
        $this->reset(['editingPackageId', 'packageRoomId', 'packageFloorId', 'packageName', 'packageDurationDays', 'packagePrice', 'packageType', 'packageOccupancyType', 'packageFeaturesInput', 'packageHasBreakfast', 'packageHasLunch', 'packageHasDinner', 'packageMealType']);

        $this->packageDurationDays = 30;
        $this->packageType = 'monthly';
        $this->packageOccupancyType = 'per_bed';
        $this->packageMealType = 'none';
    }

    public function deletePackage(string $id): void
    {
        $package = Package::whereHas('listing', fn ($query) => $this->scopePartnerRecords($query))
            ->where('listing_id', $this->listingId)->findOrFail($id);
        $package->delete();
        session()->flash('success', 'Package deleted successfully.');
    }



    public function render()

    {

        $listing = $this->listingId

            ? Listing::with(['images', 'floors.rooms.images', 'meta', 'packages.room'])->find($this->listingId)

            : null;



        $pgCategory   = Category::whereIn('name', ['PG / Hostel', 'Room Rental'])->first();

        $customFields = $pgCategory

            ? CustomField::where('category_id', $pgCategory->id)->orderBy('sort_order')->get()

            : collect();



        $packages = $listing?->packages ?? collect();

        $rooms = $listing?->floors?->flatMap->rooms->values() ?? collect();



        return view('livewire.partner.pg-listing', compact('listing', 'customFields', 'packages', 'rooms'))

            ->layout('layouts.app', [

                'panelName'    => 'Partner Panel',

                'pageTitle'    => $this->listingId ? 'Edit PG / Room Listing' : 'Add PG / Room Listing',

                'pageSubtitle' => 'Manage your property, floors and rooms',

                'sidebarLinks' => view('partials.sidebar-partner'),

            ]);

    }

}
