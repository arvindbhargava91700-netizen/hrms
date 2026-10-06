<?php



namespace App\Livewire\Partner;



use App\Models\Category;

use App\Models\CustomField;

use App\Models\Listing;

use App\Models\ListingImage;

use App\Models\ListingMeta;

use App\Models\Package;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;

use Livewire\WithFileUploads;



class GenericListing extends Component

{

    use WithFileUploads;
    use HasPartnerWorkspaceScope;



    #[Locked]
    public ?string $listingId   = null;

    public ?int    $categoryId  = null;

    public string  $activeTab   = 'basic';



    public string $title       = '';

    public string $description = '';

    public string $address     = '';

    public string $latitude    = '';

    public string $longitude   = '';

    public $newImages = [];

    public array $metaValues   = [];



    public string $packageName = '';

    public int $packageDurationDays = 30;

    public string $packagePrice = '';

    public string $packageType = 'monthly';

    public string $packageFeaturesInput = '';



    public function mount(?string $id = null, ?int $category = null): void

    {
        $this->selectedWorkspacePartnerId();
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess($id ? 'listing_update' : 'listing_create'), 403);

        $this->listingId  = $id;

        $this->categoryId = $category;



        if ($id) {

            $listing = $this->scopePartnerRecords(Listing::with(['meta']))->findOrFail($id);

            $this->title       = $listing->title;

            $this->description = $listing->description ?? '';

            $this->address     = $listing->address ?? '';

            $this->latitude    = (string) ($listing->lat ?? '');

            $this->longitude   = (string) ($listing->lng ?? '');

            $this->categoryId  = $listing->category_id;

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

            'categoryId'  => 'required|exists:categories,id',

            'newImages.*' => 'nullable|image|max:2048',

        ]);



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

                'category_id' => $this->categoryId,

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

        $this->activeTab = 'packages';

        session()->flash('success', 'Basic info saved! Now add packages.');

    }



    public function deleteImage(int $id): void

    {

        $img = ListingImage::whereHas('listing', fn ($query) => $this->scopePartnerRecords($query))->findOrFail($id);

        Storage::disk('public')->delete($img->image_path);

        $img->delete();

    }



    public function saveCustomFields(): void

    {

        $this->scopePartnerRecords(Listing::query())->findOrFail($this->listingId);
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
            ? $this->scopePartnerRecords(Listing::with(['images', 'meta', 'category', 'packages']))->find($this->listingId)

            : null;



        $category = $this->categoryId

            ? Category::find($this->categoryId)

            : ($listing?->category ?? null);



        $customFields = $category

            ? CustomField::where('category_id', $category->id)->orderBy('sort_order')->get()

            : collect();



        $allCategories = Category::active()->get();



        $packages = $listing?->packages ?? collect();



        return view('livewire.partner.generic-listing', compact('listing', 'category', 'customFields', 'allCategories', 'packages'))

            ->layout('layouts.app', [

                'panelName'    => 'Partner Panel',

                'pageTitle'    => $this->listingId ? 'Edit Listing' : 'Add New Listing',

                'pageSubtitle' => 'Fill in your service details',

                'sidebarLinks' => view('partials.sidebar-partner'),

            ]);

    }

}
