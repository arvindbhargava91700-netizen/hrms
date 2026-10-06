<?php

namespace App\Livewire\Partner\Reports;

use App\Models\Listing;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;

class OccupancyReport extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryId = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingCategoryId() { $this->resetPage(); }

    public function exportCsv()
    {
        $query = $this->getBaseQuery();

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Title', 'Category', 'Total Capacity', 'Occupied Beds', 'Available Beds', 'Occupancy (%)']);

            $query->chunk(200, function ($listings) use ($output) {
                foreach ($listings as $listing) {
                    $roomCapacity = (int) $listing->rooms_sum_capacity;
                    $roomAvailable = (int) $listing->rooms_sum_available_beds;
                    $roomOccupied = $roomCapacity - $roomAvailable;
                    
                    $shiftCapacity = (int) $listing->shifts_sum_max_members;
                    $shiftOccupied = (int) $listing->shifts_enrolled_sum;
                    $shiftAvailable = max(0, $shiftCapacity - $shiftOccupied);
                    
                    $capacity = $roomCapacity + $shiftCapacity;
                    $occupied = $roomOccupied + $shiftOccupied;
                    $available = $roomAvailable + $shiftAvailable;
                    $occupancyPercentage = $capacity > 0 ? round(($occupied / $capacity) * 100, 2) : 0;

                    fputcsv($output, [
                        $listing->title,
                        $listing->category->name ?? 'N/A',
                        $capacity,
                        $occupied,
                        $available,
                        $occupancyPercentage . '%'
                    ]);
                }
            });
            fclose($output);
        }, 'occupancy-report-' . now()->format('Y-m-d_His') . '.csv');
    }

    private function getBaseQuery()
    {
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $enrolledSub = \Illuminate\Support\Facades\DB::table('subscriptions')
            ->join('bookings', 'subscriptions.booking_id', '=', 'bookings.id')
            ->join('listing_shifts', 'bookings.shift_id', '=', 'listing_shifts.id')
            ->where('subscriptions.status', 'active')
            ->whereColumn('listing_shifts.listing_id', 'listings.id')
            ->selectRaw('COALESCE(SUM(' . DB::getTablePrefix() . 'subscriptions.beds_booked), 0)');

        $query = Listing::with(['category'])
            ->withSum('rooms', 'capacity')
            ->withSum('rooms', 'available_beds')
            ->withSum('shifts', 'max_members')
            ->selectSub($enrolledSub, 'shifts_enrolled_sum');
        if (!$isSuperAdmin) {
            $query->where('partner_id', $partnerId);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%');
            });
        }
        if (!empty($this->categoryId)) {
            $query->where('category_id', $this->categoryId);
        }

        return $query;
    }

    public function getStatsProperty()
    {
        $query = $this->getBaseQuery();
        $totalCapacity = 0;
        $totalAvailable = 0;

        foreach ($query->get() as $listing) {
            $roomCapacity = (int) $listing->rooms_sum_capacity;
            $roomAvailable = (int) $listing->rooms_sum_available_beds;
            $shiftCapacity = (int) $listing->shifts_sum_max_members;
            $shiftOccupied = (int) $listing->shifts_enrolled_sum;
            
            $totalCapacity += $roomCapacity + $shiftCapacity;
            $totalAvailable += $roomAvailable + max(0, $shiftCapacity - $shiftOccupied);
        }

        $totalOccupied = $totalCapacity - $totalAvailable;

        return [
            'total_capacity' => $totalCapacity,
            'total_occupied' => $totalOccupied,
            'total_available' => $totalAvailable,
            'occupancy_rate' => $totalCapacity > 0 ? round(($totalOccupied / $totalCapacity) * 100, 1) : 0,
        ];
    }

    public function render()
    {
        $query = $this->getBaseQuery();
        $records = $query->latest()->paginate(15);
        $categories = Category::orderBy('name')->get();

        return view('livewire.partner.reports.occupancy-report', [
            'records' => $records,
            'stats' => $this->stats,
            'categories' => $categories
        ])
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Occupancy Report',
                'pageSubtitle' => 'View occupancy and fillup for your listings',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
