<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\VisitBooking;

class Visits extends Component
{
    use WithPagination;
    use HasPartnerWorkspaceScope;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('visit_viewany') || auth()->user()->canAccess('visit_viewown'), 403);
    }

    public function render()
    {
        $query = $this->scopePartnerRecords(VisitBooking::with('customer','listing'));
            
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('visit_viewany')) {
            $query->where('created_by', auth()->id());
        }

        $visits = $query->latest()->paginate(20);

        return view('livewire.partner.visits', compact('visits'))
            ->layout('layouts.app', [
                'panelName' => 'Partner Panel',
                'pageTitle' => 'Visit Requests',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }

    public function approve(string $id, ?string $note = null)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('visit_update'), 403);
        $visit = $this->scopePartnerRecords(VisitBooking::query())->where('id', $id)->where('status', 'pending')->firstOrFail();
        $data = ['status' => 'accepted'];
        if ($note !== null) $data['note'] = $note;
        $visit->update($data);

        try {
            $customer = $visit->customer;
            if ($customer?->fcm_token) {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $customer->fcm_token,
                    'Visit Accepted',
                    "Your visit request on {$visit->visit_date} at {$visit->visit_time} has been accepted.",
                    ['visit_id' => $visit->id]
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Partner approve notify failed: ' . $e->getMessage());
        }

        session()->flash('success', 'Visit accepted');
        $this->resetPage();
    }

    public function reject(string $id, ?string $note = null)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('visit_update'), 403);
        $visit = $this->scopePartnerRecords(VisitBooking::query())->where('id', $id)->where('status', 'pending')->firstOrFail();
        $data = ['status' => 'rejected'];
        if ($note !== null) $data['note'] = $note;
        $visit->update($data);

        try {
            $customer = $visit->customer;
            if ($customer?->fcm_token) {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $customer->fcm_token,
                    'Visit Rejected',
                    "Your visit request on {$visit->visit_date} at {$visit->visit_time} has been rejected.",
                    ['visit_id' => $visit->id]
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Partner reject notify failed: ' . $e->getMessage());
        }

        session()->flash('success', 'Visit rejected');
        $this->resetPage();
    }
}
