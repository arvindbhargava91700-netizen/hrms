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
use App\Models\ListingShift;
use App\Models\ListingTrainer;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageListing extends Component
{

    use WithFileUploads;
    use HasPartnerWorkspaceScope;



    #[Locked]
    public ?string $listingId   = null;
    public ?int    $categoryId  = null;
    public ?string $genderType  = null;
    public string  $activeTab   = 'basic';

    public function updatedCategoryId($value): void
    {
        if ($value) {
            $this->categoryId = (int) $value;
        }
    }



    public string $title       = '';
    public string $description = '';
    public float $listingSecurityDeposit = 0;
    public string $address     = '';
    public string $city        = '';
    public string $state       = '';
    public string $pincode     = '';
    public string $landmark    = '';
    public string $latitude    = '';
    public string $longitude   = '';
    public string $searchKeywords = '';
    public string $openingTime = '';
    public string $closingTime = '';
    public array $newListingImages = [];
    public array $metaValues   = [];

    // Packages
    public ?string $packageRoomId = null;
    public ?int $packageFloorId = null;
    public ?string $editingPackageId = null;

    private function markAsPendingIfPartnerEdited(?Listing $listing): void
    {
        if (!$listing) return;
        if (auth()->user()->role !== 'super_admin' && in_array($listing->status, ['approved', 'rejected'])) {
            $listing->update(['status' => 'pending']);
        }
    }



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



    // Shifts

    public string $shiftName    = 'morning';
    public string $startTime    = '06:00';
    public string $endTime      = '08:00';
    public int    $maxMembers   = 30;
    public float  $shiftFee     = 0;



    // Trainers

    public ?int   $editingTrainerId    = null;
    public string $trainerName         = '';
    public string $trainerSpec         = '';
    public string $trainerDesc         = '';
    public int    $trainerExp          = 0;
    public array $newTrainerPhotos = [];
    public array $existingTrainerPhotos = [];



    // Floor Form
    public ?string $editingFloorId = null;
    public string $floorName   = '';
    public int    $floorNumber = 0;



    // Room Form (tied to a floor)

    public ?string $editingRoomId   = null;
    public ?string $roomFloorId     = null;
    public string $roomNumber      = '';
    public string $roomType        = '1BHK';
    public int    $capacity        = 1;
    public float  $securityDeposit = 500;
    public array  $newRoomImages = [];
    public array  $existingRoomImages = [];

    protected function persistBasic(?string $status, bool $requireFullBasicInfo): void
    {

        if ($requireFullBasicInfo) {

            $this->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'address'     => 'required|string',
                'city'        => 'nullable|string',
                'state'       => 'nullable|string',
                'pincode'     => 'nullable|string',
                'landmark'    => 'nullable|string|max:255',
                'listingSecurityDeposit' => 'nullable|numeric|min:0',
                'latitude'    => 'required|numeric|between:-90,90',
                'longitude'   => 'required|numeric|between:-180,180',
                'categoryId'  => 'required|exists:categories,id',
                'genderType'  => 'nullable|in:boys,girls,co-living',
                'newListingImages.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);



            $existingCount = $this->listingId ? ListingImage::where('listing_id', $this->listingId)->count() : 0;

            if (($existingCount + count($this->newListingImages)) < 3) {

                $this->addError('newListingImages', 'Minimum 3 photos are mandatory before submission.');

                return;

            }

        } else {

            $this->validate([

                'categoryId'  => 'required|exists:categories,id',
                'genderType'  => 'nullable|in:boys,girls,co-living',
                'newListingImages.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',

            ]);

        }

        if (auth()->user()->role !== 'super_admin') {
            $activeSub = \App\Models\PartnerSubscription::with('package')->where('partner_id', $this->getPartnerId())->where('status', 'active')->first();
            if (!$activeSub || !$activeSub->package) {
                session()->flash('error', 'You do not have an active package. Please purchase one to create or update listings.');
                return;
            }
            $package = $activeSub->package;
            // Check category limit
            if ($package->category_limit !== null) {
                $query = \App\Models\Listing::where('partner_id', $this->getPartnerId());
                if ($this->listingId) {
                    $query->where('id', '!=', $this->listingId);
                }
                
                $existingCategoryIds = $query->pluck('category_id')->unique()->toArray();
                
                if (!in_array((int)$this->categoryId, $existingCategoryIds)) {
                    // Adding a new category
                    if (count($existingCategoryIds) >= $package->category_limit) {
                        session()->flash('error', 'Your current package restricts you to a maximum of ' . $package->category_limit . ' category/categories.');
                        return;
                    }
                }
            }

            // Check listing limit
            if (!$this->listingId && $package->listing_limit !== null) {
                $existingCount = \App\Models\Listing::where('partner_id', $this->getPartnerId())->count();
                if ($existingCount >= $package->listing_limit) {
                    session()->flash('error', "Your current package allows a maximum of {$package->listing_limit} listings. Please upgrade your package.");
                    return;
                }
            }
        }

        $listingData = [

            'category_id'  => $this->categoryId,

            'gender_type'  => $this->genderType ?: null,

            'title'        => $this->title ?: '',

            'description'  => $this->description ?: null,

            'address'      => $this->address ?: null,

            'city'         => $this->city ?: null,

            'state'        => $this->state ?: null,

            'pincode'      => $this->pincode ?: null,

            'landmark'     => $this->landmark ?: null,

            'security_deposit' => $this->listingSecurityDeposit,

            'lat'          => filled($this->latitude) ? $this->latitude : null,

            'lng'          => filled($this->longitude) ? $this->longitude : null,

            'search_keywords' => $this->searchKeywords ?: null,

            'opening_time' => filled($this->openingTime) ? $this->openingTime : null,

            'closing_time' => filled($this->closingTime) ? $this->closingTime : null,

        ];



        // Auto-generate keywords if none provided

        if (empty($listingData['search_keywords'])) {

            $catName = Category::find($this->categoryId)?->name ?? '';

            $listingData['search_keywords'] = implode(', ', array_filter([$this->title, $catName, $this->city, $this->state, $this->landmark]));

        }



        if ($this->listingId) {

            $listingQuery = Listing::query();

            if (auth()->user()->role !== 'super_admin') {

                $listingQuery->where('partner_id', $this->getPartnerId());

            }

            $listing = $listingQuery->findOrFail($this->listingId);

            

            $statusToSave = $status ?? $listing->status;

            if (auth()->user()->role !== 'super_admin' && in_array($listing->status, ['approved', 'rejected'])) {

                $statusToSave = 'pending';

            }

            $listingData['status'] = $statusToSave;



            if (empty($listing->created_by)) {
                $listingData['created_by'] = auth()->id();
            }

            $listing->update($listingData);

        } else {

            $listingData['status'] = $status ?? 'draft';

            $listing = Listing::create(array_merge([

                'partner_id' => $this->requirePartnerIdForWrite(),
                'created_by' => auth()->id(),

            ], $listingData));

            $this->listingId = $listing->id;

        }



        foreach ($this->newListingImages as $img) {

            $path = $this->resizeAndStoreImage($img, 'listings', 800, 600);

            ListingImage::create([

                'listing_id' => $listing->id,

                'image_path' => $path,

                'sort_order' => $listing->images()->count(),

            ]);

        }



        $this->newListingImages = [];

        

        if ($this->activeTab === 'basic') {

            $this->goToNextTab();

        }



        session()->flash('success', $requireFullBasicInfo ? 'Basic info saved!' : 'Draft saved. You can continue editing anytime.');

    }



    public function getAvailableTabsProperty(): array

    {

        if (!$this->categoryId) return ['basic'];

        $category = Category::find($this->categoryId);

        if (!$category) return ['basic', 'fields'];



        $tabs = ['basic'];

        if ($category->has_shifts) $tabs[] = 'shifts';

        if ($category->has_trainers) $tabs[] = 'trainers';

        if ($category->has_rooms) {

            $tabs[] = 'floors';

            $tabs[] = 'rooms';

        }

        if ($category->has_packages) $tabs[] = 'packages';

        $tabs[] = 'fields';

        $tabs[] = 'keywords';



        return $tabs;

    }



    public function goToNextTab(): void

    {

        $tabs = $this->availableTabs;

        $idx = array_search($this->activeTab, $tabs);

        if ($idx !== false && isset($tabs[$idx + 1])) {

            $this->activeTab = $tabs[$idx + 1];

        }

    }



    public function goToPreviousTab(): void

    {

        $tabs = $this->availableTabs;

        $idx = array_search($this->activeTab, $tabs);

        if ($idx !== false && isset($tabs[$idx - 1])) {

            $this->activeTab = $tabs[$idx - 1];

        }

    }



    public function removeNewListingImage(int $index): void

    {

        if (isset($this->newListingImages[$index])) {

            unset($this->newListingImages[$index]);

            $this->newListingImages = array_values($this->newListingImages);

        }

    }



    public function removeNewTrainerPhoto(int $index): void

    {

        if (isset($this->newTrainerPhotos[$index])) {

            unset($this->newTrainerPhotos[$index]);

            $this->newTrainerPhotos = array_values($this->newTrainerPhotos);

        }

    }



    public function removeNewRoomImage(int $index): void

    {

        if (isset($this->newRoomImages[$index])) {

            unset($this->newRoomImages[$index]);

            $this->newRoomImages = array_values($this->newRoomImages);

        }

    }



    public function mount(?string $id = null): void

    {
        $this->selectedWorkspacePartnerId();
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess($id ? 'listing_update' : 'listing_create'), 403);

        $this->listingId  = $id;

        $this->categoryId = request()->query('category') ?: null;

        if (!$id && !$this->categoryId && auth()->user()->isPartner()) {
            $userCategories = auth()->user()->categories;
            if ($userCategories && $userCategories->count() === 1) {
                $this->categoryId = $userCategories->first()->id;
            }
        }

        if ($id) {

            $listingQuery = Listing::with(['meta']);

            if (auth()->user()->role !== 'super_admin') {

                $listingQuery->where('partner_id', $this->getPartnerId());

            }

            $listing = $listingQuery->findOrFail($id);

            if (auth()->user()->role !== 'super_admin' && !in_array($listing->status, ['draft', 'approved', 'rejected', 'pending'])) {

                session()->flash('error', 'You cannot edit a listing while it is ' . $listing->status . '.');

                $this->redirect(route('partner.listings'));

                return;

            }



            if (auth()->user()->role !== 'super_admin' && in_array($listing->status, ['approved', 'rejected'])) {

                $listing->update(['status' => 'pending']);

                $listing->refresh();

            }



            $this->title       = $listing->title;

            $this->description = $listing->description ?? '';

            $this->address     = $listing->address ?? '';

            $this->city        = $listing->city ?? '';

            $this->state       = $listing->state ?? '';

            $this->pincode     = $listing->pincode ?? '';

            $this->landmark    = $listing->landmark ?? '';



            $this->latitude    = (string) ($listing->lat ?? '');

            $this->longitude   = (string) ($listing->lng ?? '');

            $this->searchKeywords = $listing->search_keywords ?? '';

            $this->listingSecurityDeposit = (float) ($listing->security_deposit ?? 0);

            $this->categoryId  = $listing->category_id;

            $this->genderType  = $listing->gender_type;

            $this->openingTime = $listing->opening_time ?? '';

            $this->closingTime = $listing->closing_time ?? '';

            foreach ($listing->meta as $m) {

                $this->metaValues[$m->custom_field_id] = $m->value;

            }

        }

    }



    public function saveBasic(): void { $this->persistBasic(null, true); }

    public function saveDraft(): void { $this->persistBasic('draft', false); }



    public function deleteImage(int $id): void

    {

        $img = ListingImage::where('listing_id', $this->listingId)->findOrFail($id);

        Storage::disk('public')->delete($img->image_path);

        $img->delete();

        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

    }



    // --- Shifts Module ---

    public function addShift(): void

    {

        $this->validate([

            'listingId' => 'required',

            'shiftName' => 'required|in:morning,evening,night',

            'startTime' => 'required',

            'endTime'   => 'required',

            'maxMembers'=> 'required|integer|min:1',

            'shiftFee'  => 'required|numeric|min:0',

        ]);



        $listing = Listing::findOrFail($this->listingId);



        $anchor = $listing->opening_time ? strtotime($listing->opening_time) : strtotime('00:00');

        $start = strtotime($this->startTime);

        $end = strtotime($this->endTime);



        $adjStart = $start;

        if ($adjStart < $anchor) {

            $adjStart += 86400;

        }



        $adjEnd = $end;

        if ($adjEnd <= $adjStart) {

            $adjEnd += 86400;

        }



        if ($listing->opening_time && $listing->closing_time) {

            $open = strtotime($listing->opening_time);

            $close = strtotime($listing->closing_time);



            if ($close <= $open) {

                $close += 86400;

            }



            if ($adjStart < $open || $adjEnd > $close) {

                $this->addError('startTime', "The selected shifting time must fall between the property's Opening Time and Closing Time.");

                return;

            }

        }



        // Check for overlaps with existing shifts

        $existingShifts = ListingShift::where('listing_id', $this->listingId)->get();

        foreach ($existingShifts as $existing) {

            $exStart = strtotime($existing->start_time);

            $exEnd = strtotime($existing->end_time);



            $adjExStart = $exStart;

            if ($adjExStart < $anchor) {

                $adjExStart += 86400;

            }



            $adjExEnd = $exEnd;

            if ($adjExEnd <= $adjExStart) {

                $adjExEnd += 86400;

            }



            if ($adjStart < $adjExEnd && $adjEnd > $adjExStart) {

                $t1 = \Carbon\Carbon::parse($existing->start_time)->format('h:i A');

                $t2 = \Carbon\Carbon::parse($existing->end_time)->format('h:i A');

                $this->addError('startTime', "This shift overlaps with an existing shift ($t1 - $t2).");

                return;

            }

        }



        ListingShift::create([

            'listing_id'  => $this->listingId,

            'shift_name'  => $this->shiftName,

            'start_time'  => $this->startTime,

            'end_time'    => $this->endTime,

            'max_members' => $this->maxMembers,

            'fee'         => $this->shiftFee,

        ]);



        $this->reset(['shiftName', 'startTime', 'endTime', 'maxMembers', 'shiftFee']);

        $this->shiftName = 'morning';

        $this->startTime = '06:00';

        $this->endTime   = '08:00';

        $this->maxMembers = 30;

        session()->flash('success', 'Shift added!');

        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

    }



    public function deleteShift(int $id): void

    {

        ListingShift::where('listing_id', $this->listingId)->findOrFail($id)->delete();

        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

    }



    // --- Trainers Module ---

    public function saveTrainer(): void

    {

        $this->validate([

            'listingId'     => 'required',

            'trainerName'   => 'required|string|max:100',

            'trainerSpec'   => 'nullable|string|max:100',

            'trainerDesc'   => 'nullable|string|max:500',

            'trainerExp'    => 'required|integer|min:0',

            'newTrainerPhotos.*' => 'image|mimes:jpeg,png,jpg,webp|max:1024',

        ]);



        $existingCount = 0;

        if ($this->editingTrainerId) {

            $existingCount = count($this->existingTrainerPhotos);

        }

        

        if (($existingCount + count($this->newTrainerPhotos)) < 3) {

            $this->addError('newTrainerPhotos', 'Minimum 3 photos are mandatory.');

            return;

        }



        $data = [

            'listing_id'       => $this->listingId,

            'name'             => $this->trainerName,

            'specialization'   => $this->trainerSpec,

            'description'      => $this->trainerDesc,

            'experience_years' => $this->trainerExp,

        ];



        if ($this->editingTrainerId) {

            $trainer = ListingTrainer::where('listing_id', $this->listingId)->findOrFail($this->editingTrainerId);

            $photos = $this->existingTrainerPhotos;

        } else {

            $photos = [];

        }



        foreach ($this->newTrainerPhotos as $photo) {

            $photos[] = $this->resizeAndStoreImage($photo, 'trainers', 600, 600);

        }



        $data['photo'] = json_encode($photos);



        if ($this->editingTrainerId) {

            $trainer->update($data);

            session()->flash('success', 'Trainer updated!');

        } else {

            ListingTrainer::create($data);

            session()->flash('success', 'Trainer added!');

        }



        $this->cancelEditTrainer();

        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

    }



    public function removeExistingTrainerPhoto(int $index): void

    {

        if (isset($this->existingTrainerPhotos[$index])) {

            $path = $this->existingTrainerPhotos[$index];

            Storage::disk('public')->delete($path);

            unset($this->existingTrainerPhotos[$index]);

            $this->existingTrainerPhotos = array_values($this->existingTrainerPhotos);



            $trainer = ListingTrainer::where('listing_id', $this->listingId)->findOrFail($this->editingTrainerId);

            $trainer->update(['photo' => json_encode($this->existingTrainerPhotos)]);

            $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

        }

    }



    public function editTrainer(int $id): void

    {

        $trainer = ListingTrainer::where('listing_id', $this->listingId)->findOrFail($id);

        $this->editingTrainerId = $trainer->id;

        $this->trainerName      = $trainer->name ?? '';

        $this->trainerSpec      = $trainer->specialization ?? '';

        $this->trainerDesc      = $trainer->description ?? '';

        $this->trainerExp       = $trainer->experience_years;

        $this->existingTrainerPhotos = json_decode($trainer->photo, true) ?: [];

        $this->newTrainerPhotos = [];

    }



    public function cancelEditTrainer(): void

    {

        $this->reset(['editingTrainerId', 'trainerName', 'trainerSpec', 'trainerDesc', 'trainerExp', 'newTrainerPhotos', 'existingTrainerPhotos']);

    }



    public function deleteTrainer(int $id): void

    {

        $trainer = ListingTrainer::where('listing_id', $this->listingId)->findOrFail($id);

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

        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

    }



    // --- Rooms/Floors Module ---

    public function addFloor(): void

    {

        $this->validate([

            'listingId'   => 'required',

            'floorName'   => 'required|string|max:100',

            'floorNumber' => 'required|integer',

        ]);



        if ($this->editingFloorId) {

            $floor = Floor::where('listing_id', $this->listingId)->findOrFail($this->editingFloorId);

            $floor->update([

                'name'         => $this->floorName,

                'floor_number' => $this->floorNumber,

            ]);

            session()->flash('success', 'Floor updated!');

        } else {

            Floor::create([

                'listing_id'   => $this->listingId,

                'name'         => $this->floorName,

                'floor_number' => $this->floorNumber,

            ]);

            session()->flash('success', 'Floor added!');

        }



        $this->cancelEditFloor();

        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

    }



    public function editFloor(string $id): void

    {

        $floor = Floor::where('listing_id', $this->listingId)->findOrFail($id);

        $this->editingFloorId = $floor->id;

        $this->floorName      = $floor->name;

        $this->floorNumber    = $floor->floor_number;

    }



    public function cancelEditFloor(): void

    {

        $this->reset(['editingFloorId', 'floorName', 'floorNumber']);

    }



    public function deleteFloor(int $id): void

    {

        Floor::where('listing_id', $this->listingId)->findOrFail($id)->delete();

        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

    }



    public function selectFloor(int $floorId): void

    {

        $this->roomFloorId = $floorId;

    }



    public function addRoom(): void

    {

        $this->validate([

            'roomFloorId'     => 'required|exists:floors,id',

            'roomNumber'      => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    $query = \App\Models\Room::where('room_number', $value)
                        ->whereHas('floor', function($q) {
                            $q->where('listing_id', $this->listingId);
                        });
                    if ($this->editingRoomId) {
                        $query->where('id', '!=', $this->editingRoomId);
                    }
                    if ($query->exists()) {
                        $fail('This room number already exists in this property.');
                    }
                }
            ],

            'capacity'        => 'required|integer|min:1',

            'newRoomImages.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',

            // 'securityDeposit' => 'nullable|numeric|min:500|multiple_of:500',
            'securityDeposit' => 'nullable|numeric|min:1',

            'packageHasBreakfast' => 'boolean',
            'packageHasLunch' => 'boolean',
            'packageHasDinner' => 'boolean',
            'packageMealType' => 'required|in:veg,non_veg,both,none',
        ]);



        if ((count($this->newRoomImages) + count($this->existingRoomImages)) < 3) {

            $this->addError('newRoomImages', 'Minimum 3 photos are mandatory.');

            return;

        }



        if ($this->editingRoomId) {

            $room = Room::whereHas('floor', fn ($query) => $query->where('listing_id', $this->listingId))
                ->findOrFail($this->editingRoomId);

            $room->update([

                'floor_id'         => $this->roomFloorId,

                'room_number'      => $this->roomNumber,

                'room_type'        => $this->roomType,

                'capacity'         => $this->capacity,

                'security_deposit' => $this->securityDeposit,

                // Do not blindly overwrite available_beds if editing, though in a real app this needs logic to adjust based on current occupancy.

            ]);

            session()->flash('success', 'Room updated!');

        } else {

            $room = Room::create([

                'floor_id'         => $this->roomFloorId,

                'room_number'      => $this->roomNumber,

                'room_type'        => $this->roomType,

                'capacity'         => $this->capacity,

                'available_beds'   => $this->capacity,

                'security_deposit' => $this->securityDeposit,

            ]);

            session()->flash('success', 'Room added!');

        }



        foreach ($this->newRoomImages as $img) {

            $path = $this->resizeAndStoreImage($img, 'rooms', 800, 600);

            RoomImage::create([

                'room_id' => $room->id,

                'image_path' => $path,

                'sort_order' => $room->images()->count(),

            ]);

        }



        $this->cancelEditRoom();

        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

    }



    public function editRoom(string $id): void

    {

        $room = Room::whereHas('floor', fn ($query) => $query->where('listing_id', $this->listingId))->findOrFail($id);

        $this->editingRoomId = $room->id;

        $this->roomFloorId   = $room->floor_id;

        $this->roomNumber    = $room->room_number;

        $this->roomType      = $room->room_type;

        $this->capacity      = $room->capacity;

        $this->securityDeposit = $room->security_deposit ?? 0;

        $this->newRoomImages = [];

        $this->existingRoomImages = $room->images->toArray();

    }



    public function cancelEditRoom(): void

    {

        $this->reset(['editingRoomId', 'roomFloorId', 'roomNumber', 'roomType', 'capacity', 'securityDeposit', 'newRoomImages', 'existingRoomImages']);

        $this->roomType = '1BHK';

        $this->capacity = 1;

        $this->securityDeposit = 500;

    }

    public function incrementSecurityDeposit()
    {
        $this->securityDeposit += 500;
    }

    public function decrementSecurityDeposit()
    {
        if ($this->securityDeposit > 500) {
            $this->securityDeposit -= 500;
        }
    }



    public function removeExistingRoomImage(int $imageId): void

    {

        $img = RoomImage::whereHas('room.floor', fn ($query) => $query->where('listing_id', $this->listingId))->findOrFail($imageId);

        Storage::disk('public')->delete($img->image_path);

        $img->delete();

        $this->existingRoomImages = Room::whereHas('floor', fn ($query) => $query->where('listing_id', $this->listingId))
            ->findOrFail($this->editingRoomId)->images->toArray();

        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

    }



    public function deleteRoom(string $id): void

    {

        Room::whereHas('floor', fn ($query) => $query->where('listing_id', $this->listingId))->findOrFail($id)->delete();

        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

    }



    // --- Packages Module ---

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

        ]);



        $listingQuery = Listing::query();

        if (auth()->user()->role !== 'super_admin') {

            $listingQuery->where('partner_id', $this->getPartnerId());

        }

        $listing = $listingQuery->findOrFail($this->listingId);

        $features = array_filter(array_map('trim', explode("\n", $this->packageFeaturesInput)));



        $roomId = null;
        if ($this->packageRoomId) {
            $room = Room::whereHas('floor', fn($q) => $q->where('listing_id', $listing->id))->findOrFail($this->packageRoomId);
            $roomId = $room->id;
        }

        $packageData = [
            'listing_id' => $this->listingId,
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
            $package = Package::where('listing_id', $this->listingId)->findOrFail($this->editingPackageId);
            $package->update($packageData);
            session()->flash('success', 'Package updated successfully.');
        } else {
            Package::create($packageData);
            session()->flash('success', 'Package added. You can add more or continue.');
        }

        $this->cancelEditPackage();

        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));

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
        $package = Package::where('listing_id', $this->listingId)->findOrFail($id);
        
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
        $package = Package::where('listing_id', $this->listingId)->findOrFail($id);
        $package->delete();
        session()->flash('success', 'Package deleted successfully.');
        $this->markAsPendingIfPartnerEdited(Listing::find($this->listingId));
    }



    // --- Finalize ---

    public function saveCustomFields(): void

    {

        foreach ($this->metaValues as $fieldId => $value) {

            ListingMeta::updateOrCreate(

                ['listing_id' => $this->listingId, 'custom_field_id' => $fieldId],

                ['value' => $value]

            );

        }

        $listingQuery = Listing::query();

        if (auth()->user()->role !== 'super_admin') {

            $listingQuery->where('partner_id', $this->getPartnerId());

        }

        $listing = $listingQuery->findOrFail($this->listingId);



        $this->markAsPendingIfPartnerEdited($listing);

        

        $this->goToNextTab();

        session()->flash('success', 'Custom fields saved.');

    }



    public function saveKeywords(): void

    {

        $listingQuery = Listing::query();

        if (auth()->user()->role !== 'super_admin') {

            $listingQuery->where('partner_id', $this->getPartnerId());

        }

        $listing = $listingQuery->findOrFail($this->listingId);



        $keywords = $this->searchKeywords;

        if (empty(trim($keywords))) {

            $catName = Category::find($this->categoryId)?->name ?? '';

            $keywords = implode(', ', array_filter([$this->title, $catName, $this->city, $this->state, $this->landmark]));

        }



        $listing->update(['search_keywords' => $keywords]);

        $this->markAsPendingIfPartnerEdited($listing);



        session()->flash('success', 'Keywords saved.');

    }



    public function submitListing(): void

    {

        $listingQuery = Listing::query();

        if (auth()->user()->role !== 'super_admin') {

            $listingQuery->where('partner_id', $this->getPartnerId());

        }

        $listing = $listingQuery->findOrFail($this->listingId);



        if ($listing->images()->count() < 3) {

            $this->addError('submitError', 'You must upload at least 3 property images before submitting.');

            return;

        }



        if ($listing->category && $listing->category->has_trainers) {

            foreach ($listing->trainers as $trainer) {

                $photos = json_decode($trainer->photo, true) ?: [];

                if (count(array_filter($photos)) < 3) {

                    $this->addError('submitError', "Trainer {$trainer->name} must have at least 3 photos.");

                    return;

                }

            }

        }



        if ($listing->category && $listing->category->has_rooms) {

            foreach ($listing->floors as $floor) {

                foreach ($floor->rooms as $room) {

                    if ($room->images()->count() < 3) {

                        $this->addError('submitError', "Room {$room->room_number} must have at least 3 photos.");

                        return;

                    }

                }

            }

        }



        $listing->update(['status' => 'pending']);

        session()->flash('success', 'Listing submitted for review!');

        if (auth()->user()->role === 'super_admin') {

            $this->redirect(route('admin.listings'));

        } else {

            $this->redirect(route('partner.listings'));

        }

    }



    public function render()

    {

        $listing = $this->listingId

            ? Listing::with(['images', 'meta', 'category', 'packages.room', 'shifts', 'trainers', 'floors.rooms.images'])->find($this->listingId)

            : null;



        $category = $this->categoryId

            ? Category::find($this->categoryId)

            : ($listing?->category ?? null);



        $customFields = $category

            ? CustomField::where('category_id', $category->id)->orderBy('sort_order')->get()

            : collect();



        $allCategories = Category::active()->get();



        $packages = $listing?->packages ?? collect();

        $rooms = $listing?->floors?->flatMap->rooms->values() ?? collect();



        $isAdmin = auth()->user()->role === 'super_admin';



        return view('livewire.partner.manage-listing', compact('listing', 'category', 'customFields', 'allCategories', 'packages', 'rooms'))

            ->layout('layouts.app', [

                'panelName'    => $isAdmin ? 'Admin Panel' : 'Partner Panel',

                'pageTitle'    => $this->listingId ? 'Edit Listing' : 'Add New Listing',

                'pageSubtitle' => 'Fill in your service details',

                'sidebarLinks' => view($isAdmin ? 'partials.sidebar-admin' : 'partials.sidebar-partner'),

            ]);

    }

}
