<?php



namespace App\Livewire\Partner;



use App\Models\Category;

use App\Models\CustomField;

use App\Models\GymShift;

use App\Models\GymTrainer;

use App\Models\Listing;

use App\Models\ListingImage;

use App\Models\ListingMeta;

use App\Models\Package;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;

use Livewire\WithFileUploads;



class GymListing extends Component

{

    use WithFileUploads;
    use HasPartnerWorkspaceScope;



    #[Locked]
    public ?string $listingId = null;

    public string $activeTab  = 'basic';



    // Basic Info

    public string $title       = '';

    public string $description = '';

    public string $address     = '';

    public string $latitude    = '';

    public string $longitude   = '';

    public $newImages = [];



    // Shifts

    public string $shiftName    = 'morning';

    public string $startTime    = '06:00';

    public string $endTime      = '08:00';

    public int    $maxMembers   = 30;

    public float  $shiftFee     = 0;



    // Trainers

    public string $trainerName         = '';

    public string $trainerSpec         = '';

    public int    $trainerExp          = 0;

    public $trainerPhoto               = null;



    // Custom Fields

    public array $metaValues = [];



    // Packages

    public string $packageName = '';

    public int $packageDurationDays = 30;

    public string $packagePrice = '';

    public string $packageType = 'monthly';

    public string $packageFeaturesInput = '';



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



    public function saveBasic(): void

    {

        $this->validate([

            'title'       => 'required|string|max:255',

            'description' => 'nullable|string',

            'address'     => 'required|string',

            'latitude'    => 'required|numeric|between:-90,90',

            'longitude'   => 'required|numeric|between:-180,180',

            'newImages.*' => 'nullable|image|max:2048',

        ]);



        $gymCategory = Category::where('name', 'Gym')->first();



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

                'lat'         => $this->latitude,

                'lng'         => $this->longitude,

            ]);

        } else {

            $listing = Listing::create([

                'partner_id'  => $this->requirePartnerIdForWrite(),
                'created_by'  => auth()->id(),

                'category_id' => $gymCategory->id,

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

        $this->activeTab = 'shifts';

        session()->flash('success', 'Basic info saved! Now add shifts.');

    }



    public function deleteImage(int $id): void

    {

        $img = ListingImage::whereHas('listing', fn ($query) => $this->scopePartnerRecords($query))->findOrFail($id);

        Storage::disk('public')->delete($img->image_path);

        $img->delete();

    }



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



        $listing = $this->scopePartnerRecords(Listing::query())->findOrFail($this->listingId);



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

        $existingShifts = GymShift::where('listing_id', $this->listingId)->get();

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



        GymShift::create([

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

    }



    public function deleteShift(int $id): void

    {

        GymShift::where('listing_id', $this->listingId)->findOrFail($id)->delete();

    }



    public function addTrainer(): void

    {

        $this->validate([

            'listingId'    => 'required',

            'trainerName'  => 'required|string|max:100',

            'trainerSpec'  => 'nullable|string|max:100',

            'trainerExp'   => 'required|integer|min:0',

            'trainerPhoto' => 'nullable|image|max:1024',

        ]);



        $photoPath = $this->trainerPhoto

            ? $this->trainerPhoto->store('trainers', 'public')

            : null;



        GymTrainer::create([

            'listing_id'       => $this->listingId,

            'name'             => $this->trainerName,

            'specialization'   => $this->trainerSpec,

            'experience_years' => $this->trainerExp,

            'photo'            => $photoPath,

        ]);



        $this->reset(['trainerName', 'trainerSpec', 'trainerExp', 'trainerPhoto']);

        session()->flash('success', 'Trainer added!');

    }



    public function deleteTrainer(int $id): void

    {

        $trainer = GymTrainer::where('listing_id', $this->listingId)->findOrFail($id);

        if ($trainer->photo) Storage::disk('public')->delete($trainer->photo);

        $trainer->delete();

    }



    public function saveCustomFields(): void

    {

        $this->validate(['listingId' => 'required']);



        foreach ($this->metaValues as $fieldId => $value) {

            ListingMeta::updateOrCreate(

                ['listing_id' => $this->listingId, 'custom_field_id' => $fieldId],

                ['value' => $value]

            );

        }



        session()->flash('success', 'Details saved successfully! Listing submitted for review.');

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

            'packageFeaturesInput' => 'nullable|string',

        ]);



        $this->scopePartnerRecords(Listing::query())->findOrFail($this->listingId);

        $features = array_filter(array_map('trim', explode("\n", $this->packageFeaturesInput)));



        Package::create([

            'listing_id' => $this->listingId,

            'name' => $this->packageName,

            'duration_days' => $this->packageDurationDays,

            'price' => $this->packagePrice,

            'type' => $this->packageType,

            'features' => empty($features) ? null : $features,

            'occupancy_type' => 'standard',

        ]);



        $this->reset(['packageName', 'packageDurationDays', 'packagePrice', 'packageType', 'packageFeaturesInput']);

        $this->packageDurationDays = 30;

        $this->packageType = 'monthly';

        session()->flash('success', 'Package added. You can add more or continue.');

    }



    public function render()

    {

        $listing = $this->listingId

            ? Listing::with(['images', 'gymShifts', 'gymTrainers', 'meta', 'packages'])->find($this->listingId)

            : null;



        $gymCategory = Category::where('name', 'Gym')->first();

        $customFields = $gymCategory

            ? CustomField::where('category_id', $gymCategory->id)->orderBy('sort_order')->get()

            : collect();



        $packages = $listing?->packages ?? collect();



        return view('livewire.partner.gym-listing', compact('listing', 'customFields', 'packages'))

            ->layout('layouts.app', [

                'panelName'    => 'Partner Panel',

                'pageTitle'    => $this->listingId ? 'Edit Gym Listing' : 'Add Gym Listing',

                'pageSubtitle' => 'Manage your gym profile, shifts and trainers',

                'sidebarLinks' => view('partials.sidebar-partner'),

            ]);

    }

}
