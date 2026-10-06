<?php

namespace App\Livewire\Partner\Hrms\LeadOrders;

use App\Models\LeadOrder;
use App\Services\OrderPipelineService;
use App\Services\TargetManagementService;
use App\Models\Lead;
use App\Models\Product;
use App\Models\LeadOrderItem;
use App\Models\PipelineStage;
use App\Models\LeadOrderStageComment;
use App\Models\LeadOrderStageApproval;
use Livewire\Component;
use Livewire\WithPagination;
use Exception;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public $currentTab = 'all';
    public $myStageIds = [];
    public $pipelineStages = [];

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('leadorder_viewAny') || auth()->user()->canAccess('leadorder_viewOwn') || auth()->user()->canAccess('leadorder_viewBranch') || auth()->user()->canAccess('leadorder_viewTeam'), 403);
        
        $this->loadStages();
        
        if (!empty($this->myStageIds)) {
            $this->currentTab = 'action_required';
        }
    }
    
    public function loadStages()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : null;
        $this->pipelineStages = PipelineStage::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->orderBy('order_index')->get();
        
        $user = auth()->user();
        if ($user->isPartner()) {
            $this->myStageIds = $this->pipelineStages->pluck('id')->map(fn ($id) => (string) $id)->values()->all();
        } else {
            $departmentId = $user->department_id;
            // Also check if user is head of any department via department_branch_heads
            $headedDepts = \App\Models\DepartmentBranchHead::where('head_id', $user->id)->pluck('department_id')->toArray();
            $myDepts = array_unique(array_filter(array_merge([$departmentId], $headedDepts)));
            
            $userId = (string) $user->id;
            $this->myStageIds = $this->pipelineStages->filter(function ($stage) use ($userId, $myDepts, $headedDepts) {
                if ($stage->assigned_to && (string) $stage->assigned_to === $userId) {
                    return true;
                }
                if (in_array($stage->department_id, $headedDepts)) {
                    return true;
                }
                if (!$stage->assigned_to && in_array($stage->department_id, $myDepts)) {
                    return true;
                }
                return false;
            })->pluck('id')->map(fn ($id) => (string) $id)->values()->all();
        }
    }


    public $rejectionNotes = '';
    public $orderToReject = null;
    public $rejectionStageId = null;
    public $viewOrder = null;
    public $commentOrderId = null;
    public $commentStageId = null;
    public $commentText = '';

    // Create Order properties
    public $newOrderLeadId = '';
    public $newOrderProductId = '';
    public $newOrderAmount = 0;
    public $newOrderGstType = 'notinclude';
    public $newOrderGstPercent = 0;
    public $newOrderQuantity = 1;
    public $newOrderDiscount = 0;
    public $newOrderPaidAmount = 0;

    protected $listeners = ['orderApproved' => '$refresh', 'orderRejected' => '$refresh'];

    public function setTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function updatedNewOrderProductId($value)
    {
        if ($value) {
            $product = Product::find($value);
            if ($product) {
                $this->newOrderAmount = (float)$product->amount;
                $this->newOrderGstType = $product->gst_type ?: 'notinclude';
                $this->newOrderGstPercent = $this->newOrderGstType === 'include' ? (float)($product->gst_percent ?: 0) : 0;
            }
        } else {
            $this->newOrderAmount = 0;
            $this->newOrderGstType = 'notinclude';
            $this->newOrderGstPercent = 0;
        }
    }

    public function getSelectedProductProperty()
    {
        if (!$this->newOrderProductId) return null;
        return Product::find($this->newOrderProductId);
    }

    public function getCalculatedTotalsProperty()
    {
        $baseUnitPrice = max(0, (float)$this->newOrderAmount);
        $qty = max(1, (int)$this->newOrderQuantity);
        $baseSubtotal = round($baseUnitPrice * $qty, 2);

        $isGstIncluded = $this->newOrderGstType === 'include' && (float)$this->newOrderGstPercent > 0;
        $gstRate = $isGstIncluded ? (float)$this->newOrderGstPercent : 0;
        $gstAmount = $isGstIncluded ? round(($baseSubtotal * $gstRate) / 100, 2) : 0;

        $unitGst = $isGstIncluded ? round(($baseUnitPrice * $gstRate) / 100, 2) : 0;
        $unitTotalWithGst = round($baseUnitPrice + $unitGst, 2);

        $grossTotal = round($baseSubtotal + $gstAmount, 2);
        $discountAmount = max(0, (float)$this->newOrderDiscount);
        $finalAmount = max(0, round($grossTotal - $discountAmount, 2));
        $paidAmount = max(0, (float)$this->newOrderPaidAmount);
        $remainingBalance = max(0, round($finalAmount - $paidAmount, 2));

        return [
            'base_unit_price'   => $baseUnitPrice,
            'unit_gst'          => $unitGst,
            'unit_total'        => $unitTotalWithGst,
            'base_subtotal'     => $baseSubtotal,
            'is_gst_included'   => $isGstIncluded,
            'gst_rate'          => $gstRate,
            'gst_amount'        => $gstAmount,
            'subtotal'          => $grossTotal,
            'discount'          => $discountAmount,
            'final_amount'      => $finalAmount,
            'paid_amount'       => $paidAmount,
            'remaining_balance' => $remainingBalance,
        ];
    }

    public function createOrder()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('leadorder_create'), 403);
        
        $rules = [
            'newOrderLeadId' => 'required',
            'newOrderProductId' => 'required',
            'newOrderAmount' => 'required|numeric|min:0',
            'newOrderQuantity' => 'required|integer|min:1',
            'newOrderDiscount' => 'nullable|numeric|min:0',
            'newOrderPaidAmount' => 'nullable|numeric|min:0',
        ];

        $this->validate($rules, [
            'newOrderLeadId.required' => 'Please select a lead.',
            'newOrderProductId.required' => 'Please select a product.',
            'newOrderAmount.required' => 'Product amount is required.',
        ]);

        // Ensure GST fields are strictly synced from the selected product
        $product = Product::find($this->newOrderProductId);
        if ($product) {
            $this->newOrderGstType = $product->gst_type ?: 'notinclude';
            $this->newOrderGstPercent = $this->newOrderGstType === 'include' ? (float)($product->gst_percent ?: 0) : 0;
        }

        // Verify the user has access to the selected lead
        $user = auth()->user();
        if (!$user->isPartner() && !$user->canAccess('lead_viewAny')) {
            $lead = Lead::findOrFail($this->newOrderLeadId);
            if ($user->canAccess('lead_viewBranch') || $user->canAccess('lead_viewTeam')) {
                abort_unless(in_array($lead->assigned_to, $user->getTeamIds()), 403);
            } else {
                abort_unless($lead->assigned_to === $user->id, 403);
            }
        }
        try {
            DB::beginTransaction();
$totals = $this->calculatedTotals;
            $partnerId = auth()->user()->isPartner() ? auth()->id() : null;
            
            // Get first stage
            $firstStage = PipelineStage::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->orderBy('order_index')->first();
            
            $order = LeadOrder::create([
                'lead_id'           => $this->newOrderLeadId,
                'base_amount'       => $totals['base_subtotal'],
                'gst_type'          => $this->newOrderGstType,
                'gst_percent'       => $totals['gst_rate'],
                'gst_amount'        => $totals['gst_amount'],
                'total_amount'      => $totals['subtotal'],
                'discount'          => $totals['discount'],
                'final_amount'      => $totals['final_amount'],
                'paid_amount'       => $totals['paid_amount'],
                'remaining_balance' => $totals['remaining_balance'],
                'approval_status'   => 'pending',
                'current_stage_id'  => $firstStage ? $firstStage->id : null,
                'payment_status'    => $totals['remaining_balance'] > 0 ? ($totals['paid_amount'] > 0 ? 'partial' : 'pending') : 'paid',
                'partner_id'        => $partnerId,
                'employee_id'       => auth()->id(),
            ]);

            // If there are no stages, mark it completed immediately
            if (!$firstStage) {
                $order->approval_status = 'completed';
                $order->target_credited = true;
                $order->save();
            }

            LeadOrderItem::create([
                'lead_order_id' => $order->id,
                'product_id'    => $this->newOrderProductId,
                'base_price'    => $totals['base_unit_price'],
                'gst_type'      => $this->newOrderGstType,
                'gst_percent'   => $totals['gst_rate'],
                'gst_amount'    => $totals['unit_gst'],
                'price'         => $totals['unit_total'],
                'quantity'      => $this->newOrderQuantity,
            ]);

            DB::commit();
            session()->flash('success', 'Order created successfully.');
            
            $this->reset([
                'newOrderLeadId',
                'newOrderProductId',
                'newOrderAmount',
                'newOrderGstType',
                'newOrderGstPercent',
                'newOrderQuantity',
                'newOrderDiscount',
                'newOrderPaidAmount'
            ]);
            $this->dispatch('close-create-modal');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    public function approveOrder($orderId)
    {
        $order = LeadOrder::findOrFail($orderId);
        $user = auth()->user();
        
        $hasPermission = $user->isPartner() 
            || $user->canAccess('leadorder_approve')
            || in_array($order->current_stage_id, $this->myStageIds)
            || !empty($this->myStageIds);

        if (!$hasPermission) {
            session()->flash('error', 'You do not have permission to approve this order.');
            return;
        }

        try {
            $pipeline = new OrderPipelineService();
            $pipeline->approveOrder($order, auth()->id());
            
            session()->flash('success', 'Order approved successfully.');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function initiateRejection($orderId, $stageId = null)
    {
        $order = LeadOrder::findOrFail($orderId);
        $stageId = $stageId ?: $this->actionStageFor($order);
        abort_unless($stageId && in_array((string) $stageId, $this->myStageIds, true), 403);
        $this->orderToReject = $orderId;
        $this->rejectionStageId = $stageId;
        $this->rejectionNotes = '';
        $this->dispatch('open-reject-modal');
    }

    public function addComment($orderId)
    {
        $order = LeadOrder::findOrFail($orderId);
        $stageId = $this->actionStageFor($order);
        abort_unless($stageId, 403);

        $this->commentOrderId = $order->id;
        $this->commentStageId = $stageId;
        $this->commentText = '';
        $this->dispatch('open-comment-modal');
    }

    public function saveComment()
    {
        $order = LeadOrder::findOrFail($this->commentOrderId);
        abort_unless($this->commentStageId && in_array((string) $this->commentStageId, $this->myStageIds, true), 403);

        $this->validate(['commentText' => 'required|string|max:2000']);

        LeadOrderStageComment::create([
            'lead_order_id' => $order->id,
            'pipeline_stage_id' => $this->commentStageId,
            'user_id' => auth()->id(),
            'comment' => $this->commentText,
        ]);

        $this->viewOrder = LeadOrder::with([
            'lead', 'employee', 'items.product', 'currentStage.assignedUser',
            'currentStage.department', 'stageComments.user', 'stageComments.stage',
        ])->findOrFail($order->id);

        $this->reset(['commentOrderId', 'commentStageId', 'commentText']);
        $this->dispatch('close-comment-modal');
        session()->flash('success', 'Comment added to the current pipeline stage.');
    }

    private function canActOnStage(LeadOrder $order): bool
    {
        $user = auth()->user();

        return $user->isPartner()
            || $user->canAccess('leadorder_approve')
            || ($order->approval_status === 'pending' && in_array((string) $order->current_stage_id, $this->myStageIds, true));
    }

    public function actionStageFor(LeadOrder $order)
    {
        if ($order->approval_status !== 'pending') {
            return null;
        }

        $approvedStageIds = $order->stageApprovals()
            ->where('status', 'approved')
            ->pluck('pipeline_stage_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return collect($this->myStageIds)
            ->first(fn ($stageId) => !in_array((string) $stageId, $approvedStageIds, true));
    }

    public function approveStage($orderId, $stageId)
    {
        $order = LeadOrder::findOrFail($orderId);
        abort_unless(in_array((string) $stageId, $this->myStageIds, true), 403);

        LeadOrderStageApproval::updateOrCreate(
            ['lead_order_id' => $order->id, 'pipeline_stage_id' => $stageId],
            ['approved_by' => auth()->id(), 'status' => 'approved', 'approved_at' => now()]
        );

        $finalStageId = PipelineStage::where('partner_id', $order->partner_id)
            ->orderByDesc('order_index')
            ->value('id');
        $isFinalStage = (string) $finalStageId === (string) $stageId;

        if ($isFinalStage) {
            $order->update(['approval_status' => 'completed', 'current_stage_id' => null]);
        } else {
            $approvedStageIds = $order->stageApprovals()
                ->where('status', 'approved')
                ->pluck('pipeline_stage_id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $nextStage = PipelineStage::where('partner_id', $order->partner_id)
                ->whereNotIn('id', $approvedStageIds)
                ->orderBy('order_index')
                ->first();

            $order->update(['current_stage_id' => $nextStage?->id]);
        }

        session()->flash('success', 'Your assigned stage was approved.');
    }

    public function showOrder($id)
    {
        $user = auth()->user();
        $order = LeadOrder::with([
            'lead', 'employee', 'items.product', 'currentStage.assignedUser',
            'currentStage.department', 'stageComments.user', 'stageComments.stage',
        ])->findOrFail($id);
        
        if (!$user->isPartner() && !$user->canAccess('leadorder_viewAny')) {
            $isCreator = $order->employee_id === $user->id;
            $isTeam = ($user->canAccess('leadorder_viewBranch') || $user->canAccess('leadorder_viewTeam')) && in_array($order->employee_id, $user->getTeamIds());
            $isStageApprover = !empty($this->myStageIds);
            
            if (!$isCreator && !$isTeam && !$isStageApprover) {
                session()->flash('error', 'You do not have permission to view this order.');
                return;
            }
        }

        $this->viewOrder = $order;
        $this->dispatch('open-view-modal');
    }

    public function confirmRejection()
    {
        $order = LeadOrder::findOrFail($this->orderToReject);
        $user = auth()->user();
        
        $hasPermission = $this->rejectionStageId
            && in_array((string) $this->rejectionStageId, $this->myStageIds, true);

        if (!$hasPermission) {
            session()->flash('error', 'You do not have permission to reject this order.');
            return;
        }

        try {
            LeadOrderStageApproval::updateOrCreate(
                ['lead_order_id' => $order->id, 'pipeline_stage_id' => $this->rejectionStageId],
                ['approved_by' => auth()->id(), 'status' => 'rejected', 'notes' => $this->rejectionNotes, 'approved_at' => now()]
            );
            $order->update(['approval_status' => 'rejected']);
            session()->flash('success', 'Order rejected.');
            $this->dispatch('close-reject-modal');
            $this->reset(['orderToReject', 'rejectionStageId', 'rejectionNotes']);
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : null;
        $query = LeadOrder::with(['lead', 'employee', 'items.product', 'currentStage.assignedUser', 'currentStage.department'])->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } });

        if (auth()->user()->role === 'employee' && !auth()->user()->canAccess('leadorder_viewAny')) {
            $query->where(function ($q) {
                if (auth()->user()->canAccess('leadorder_viewBranch') || auth()->user()->canAccess('leadorder_viewTeam')) {
                    $q->whereIn('employee_id', auth()->user()->getTeamIds());
                } else {
                    $q->where('employee_id', auth()->id());
                }
                
                // Allow user to see orders if they are an assigned approver on any stage
                if (!empty($this->myStageIds)) {
                    $q->orWhere('approval_status', 'pending');
                }
            });
        }

        // Scope orders based on the tab
        if ($this->currentTab === 'action_required') {
            $query->where('approval_status', 'pending')
                ->where(function ($stageQuery) {
                    foreach ($this->myStageIds as $stageId) {
                        $stageQuery->orWhere(function ($assignedStageQuery) use ($stageId) {
                            $assignedStageQuery->whereDoesntHave('stageApprovals', function ($approvalQuery) use ($stageId) {
                                $approvalQuery->where('pipeline_stage_id', $stageId);
                            });
                        });
                    }
                });
        } elseif ($this->currentTab === 'completed') {
            $query->where('approval_status', 'completed');
        } elseif ($this->currentTab === 'rejected') {
            $query->where('approval_status', 'rejected');
        } elseif ($this->currentTab === 'all') {
            // No status filter, show all
        } else {
            // Specific stage tab (e.g. final approvd)
            $selectedStage = $this->pipelineStages->firstWhere('id', $this->currentTab);
            $isAssignedToThisStage = $selectedStage && ($selectedStage->assigned_to === auth()->id() || in_array($this->currentTab, $this->myStageIds));
            
            if ($isAssignedToThisStage) {
                $query->where(function($q) {
                    $q->where('current_stage_id', $this->currentTab)
                      ->orWhere('approval_status', 'pending');
                });
            } else {
                $query->where('current_stage_id', $this->currentTab)->where('approval_status', 'pending');
            }
        }



        $orders = $query->latest()->paginate(10);

        // Filter available leads in the Create Order modal based on lead permissions
        $leadsQuery = Lead::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('status', 'won');
        $user = auth()->user();
        if (!$user->isPartner() && !$user->canAccess('lead_viewAny')) {
            if ($user->canAccess('lead_viewBranch') || $user->canAccess('lead_viewTeam')) {
                $leadsQuery->whereIn('assigned_to', $user->getTeamIds());
            } else {
                // OurView or only create — show only leads assigned to themselves
                $leadsQuery->where('assigned_to', $user->id);
            }
        }
        $leads = $leadsQuery->latest()->get();
        $products = Product::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->latest()->get();

        return view('livewire.partner.hrms.lead-orders.index', [
            'orders' => $orders,
            'availableLeads' => $leads,
            'availableProducts' => $products
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Order Pipeline',
            'pageSubtitle' => 'Manage and approve customer orders',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
