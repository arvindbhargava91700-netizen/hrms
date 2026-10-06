<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Lead;
use App\Models\CustomerVisit;
use App\Models\LeadOrder;
use App\Models\LeadOrderItem;
use App\Models\LeadOrderStageApproval;
use App\Models\LeadOrderStageComment;
use App\Models\PipelineStage;
use App\Services\OrderPipelineService;
use Illuminate\Support\Facades\DB;

class CrmController extends Controller
{
    /**
     * Get Leads
     */
    public function getLeads(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        
        $query = Lead::where('partner_id', $partnerId)
            ->where(function ($q) {
                $q->whereNull('assigned_to')
                  ->orWhereHas('assignedTo', fn ($uq) => $uq->where('status', 'active'));
            })
            ->with(['assignedTo', 'orders', 'visits']);

        if (!$user->isPartner() && !$user->canAccess('lead_viewAny')) {
            if ($user->canAccess('lead_viewTeam')) {
                $query->whereIn('assigned_to', $user->getTeamIds());
            } else {
                $query->where('assigned_to', $user->id);
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $leads = $query->latest()->get();

        $data = $leads->map(function (Lead $lead) {
            $item = $lead->attributesToArray();

            $item['assigned_to_name'] = optional($lead->assignedTo)->name;
            $item['assigned_to_code'] = optional($lead->assignedTo)->employee_code;
            $item['total_order_value'] = (float) $lead->orders->sum('total_amount');
            $item['total_final_amount'] = (float) $lead->orders->sum('final_amount');
            $item['orders_count'] = $lead->orders->count();
            $item['visits_count'] = $lead->visits->count();
            $item['created_date'] = $lead->created_at ? $lead->created_at->format('M d, Y') : null;
            $item['created_time'] = $lead->created_at ? $lead->created_at->format('h:i A') : null;
            $item['orders'] = $lead->orders->map(fn ($o) => [
                'id'              => $o->id,
                'total_amount'    => (float) $o->total_amount,
                'final_amount'    => (float) $o->final_amount,
                'paid_amount'     => (float) $o->paid_amount,
                'payment_status'  => $o->payment_status,
                'approval_status' => $o->approval_status,
                'order_date'      => $o->created_at ? $o->created_at->format('Y-m-d') : null,
            ])->values();
            $item['visits'] = $lead->visits->map(fn ($v) => [
                'id'            => $v->id,
                'visit_purpose' => $v->visit_purpose,
                'notes'         => $v->notes,
                'visit_date'    => $v->visit_date
                    ? \Illuminate\Support\Carbon::parse($v->visit_date)->format('Y-m-d')
                    : ($v->created_at ? $v->created_at->format('Y-m-d') : null),
            ])->values();

            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Create Lead
     */
    public function createLead(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        if (!$user->isPartner() && !$user->canAccess('lead_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:new,first_call,interested,meeting_scheduled,customer_visit,quotation,negotiation,won,lost',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::create([
            'partner_id' => $user->isPartner() ? $user->id : $user->parent_id,
            'customer_name' => $request->customer_name,
            'customer_mobile' => $request->customer_mobile,
            'email' => $request->email,
            'company_name' => $request->company_name,
            'status' => $request->status ?? 'new',
            'assigned_to' => $request->assigned_to ?? $user->id,
            'notes' => $request->notes,
        ]);

        return response()->json(['status' => 'success', 'data' => $lead], 201);
    }

    /**
     * Update Lead Status
     */
    public function updateLeadStatus(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $lead = Lead::where('partner_id', $partnerId)->findOrFail($id);

        if (!$user->isPartner() && !$user->canAccess('lead_update') && $lead->assigned_to !== $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate(['status' => 'required|in:new,first_call,interested,meeting_scheduled,customer_visit,quotation,negotiation,won,lost']);
        $lead->update(['status' => $request->status]);

        return response()->json(['status' => 'success', 'data' => $lead]);
    }

    public function updateLead(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $lead = Lead::where('partner_id', $partnerId)->findOrFail($id);

        if (!$user->isPartner() && !$user->canAccess('lead_update')) {
            if ($user->canAccess('lead_viewTeam')) {
                if (!in_array($lead->assigned_to, $user->getTeamIds())) {
                    return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
                }
            } else if ($lead->assigned_to !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:new,first_call,interested,meeting_scheduled,customer_visit,quotation,negotiation,won,lost',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $lead->update([
            'customer_name' => $request->customer_name,
            'customer_mobile' => $request->customer_mobile,
            'email' => $request->email,
            'company_name' => $request->company_name,
            'status' => $request->status ?? $lead->status,
            'assigned_to' => $request->assigned_to ?? $lead->assigned_to,
            'notes' => $request->notes,
        ]);

        return response()->json(['status' => 'success', 'data' => $lead]);
    }

    public function deleteLead($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $lead = Lead::where('partner_id', $partnerId)->findOrFail($id);

        if (!$user->isPartner() && !$user->canAccess('lead_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $lead->delete();

        return response()->json(['status' => 'success', 'message' => 'Lead deleted successfully.']);
    }

    /**
     * Present a customer visit in the shape of the web Customer Visits page
     */
    private function presentVisit(CustomerVisit $visit): array
    {
        return [
            'id'           => $visit->id,
            'lead'         => $visit->lead ? [
                'id'              => $visit->lead->id,
                'customer_name'   => $visit->lead->customer_name,
                'customer_mobile' => $visit->lead->customer_mobile,
                'company_name'    => $visit->lead->company_name,
            ] : null,
            'employee_id'  => $visit->employee_id,
            'employee'     => $visit->employee ? [
                'id'            => $visit->employee->id,
                'name'          => $visit->employee->name,
                'employee_code' => $visit->employee->employee_code,
            ] : null,
            'visit_date'   => $visit->visit_date ? $visit->visit_date->format('M d, Y') : null,
            'visit_time'   => $visit->visit_date ? $visit->visit_date->format('h:i A') : null,
            'location'     => $visit->location,
            'gps_location' => $visit->gps_location,
            'purpose'      => $visit->purpose,
            'notes'        => $visit->notes,
            'status'       => $visit->status,
            'created_at'   => $visit->created_at ? $visit->created_at->format('M d, Y h:i A') : null,
        ];
    }

    /**
     * Get Customer Visits
     */
    public function getCustomerVisits(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = CustomerVisit::with(['lead', 'employee'])->where('partner_id', $partnerId);

        if ($user->role === 'employee' && !$user->canAccess('customervisit_viewAny')) {
            if ($user->canAccess('customervisit_viewBranch') || $user->canAccess('customervisit_viewTeam')) {
                $query->whereIn('employee_id', $user->getTeamIds());
            } else {
                $query->where('employee_id', $user->id);
            }
        }

        if ($request->filled('search')) {
            $query->whereHas('lead', function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_mobile', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->integer('per_page', 10);

        $visits = $query->latest('visit_date')->paginate(max(1, $perPage));

        $data = collect($visits->items())->map(fn ($visit) => $this->presentVisit($visit));

        return response()->json([
            'status' => 'success',
            'data' => $data->values(),
            'pagination' => [
                'total'        => $visits->total(),
                'per_page'     => $visits->perPage(),
                'current_page' => $visits->currentPage(),
                'last_page'    => $visits->lastPage(),
            ],
        ]);
    }

    /**
     * Create Customer Visit (mirrors web CustomerVisits createVisit)
     */
    public function createCustomerVisit(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        if (!$user->isPartner() && !$user->canAccess('customervisit_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'visit_date' => 'required|date',
            'visit_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:scheduled,completed,cancelled,rescheduled',
            'gps_location' => 'nullable|string|max:255',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
        ]);

        $gpsLocation = $request->filled('gps_location')
            ? $request->gps_location
            : ($request->filled('location_lat') || $request->filled('location_lng')
                ? trim((string) $request->location_lat) . ',' . trim((string) $request->location_lng)
                : null);

        $visit = CustomerVisit::create([
            'partner_id' => $user->isPartner() ? $user->id : $user->parent_id,
            'employee_id' => $user->id,
            'lead_id' => $request->lead_id,
            'visit_date' => $request->filled('visit_time') ? $request->visit_date . ' ' . $request->visit_time : $request->visit_date,
            'location' => $request->location,
            'gps_location' => $gpsLocation ?? null,
            'purpose' => $request->purpose,
            'notes' => $request->notes,
            'status' => $request->status ?? 'scheduled',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Visit scheduled successfully.',
            'data' => $this->presentVisit($visit->load('lead')),
        ], 201);
    }

    public function updateCustomerVisit(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $visit = CustomerVisit::where('partner_id', $partnerId)->findOrFail($id);

        if (!$user->isPartner() && !$user->canAccess('customervisit_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'visit_date' => 'required|date',
            'visit_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:scheduled,completed,cancelled,rescheduled',
            'gps_location' => 'nullable|string|max:255',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
        ]);

        $gpsLocation = $request->filled('gps_location')
            ? $request->gps_location
            : ($request->filled('location_lat') || $request->filled('location_lng')
                ? trim((string) $request->location_lat) . ',' . trim((string) $request->location_lng)
                : null);

        $visit->update([
            'lead_id' => $request->lead_id,
            'visit_date' => $request->filled('visit_time') ? $request->visit_date . ' ' . $request->visit_time : $request->visit_date,
            'location' => $request->location,
            'gps_location' => $gpsLocation,
            'purpose' => $request->purpose,
            'notes' => $request->notes,
            'status' => $request->status ?? $visit->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Visit updated successfully.',
            'data' => $this->presentVisit($visit->load('lead')),
        ]);
    }

    public function deleteCustomerVisit($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $visit = CustomerVisit::where('partner_id', $partnerId)->findOrFail($id);

        if (!$user->isPartner() && !$user->canAccess('customervisit_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $visit->delete();

        return response()->json(['status' => 'success', 'message' => 'Visit deleted successfully.']);
    }

    /**
     * Get Lead Orders (mirrors web Lead Orders Pipeline page)
     */
    public function getOrders(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $myStageIds = $this->myStageIdsFor($user, $partnerId);

        $query = LeadOrder::with(['lead', 'employee', 'items.product', 'currentStage.assignedUser', 'currentStage.department'])
            ->where('partner_id', $partnerId);

        if (!$user->isPartner() && !$user->canAccess('leadorder_viewAny')) {
            $query->where(function ($q) use ($user, $myStageIds) {
                if ($user->canAccess('leadorder_viewBranch') || $user->canAccess('leadorder_viewTeam')) {
                    $q->whereIn('employee_id', $user->getTeamIds());
                } else {
                    $q->where('employee_id', $user->id);
                }
                if (!empty($myStageIds)) {
                    $q->orWhere('approval_status', 'pending');
                }
            });
        }

        // Tab scoping like the web page (all / action_required / completed / rejected / stage)
        $tab = $request->input('tab', $request->input('tabs'));
        if (!$request->filled('tab') && !$request->filled('tabs')) {
            $tab = !empty($myStageIds) ? 'action_required' : 'all';
        }
        if ($tab === 'action_required') {
            $query->where('approval_status', 'pending')
                ->where(function ($stageQuery) use ($myStageIds) {
                    foreach ($myStageIds as $stageId) {
                        $stageQuery->orWhere(function ($assignedStageQuery) use ($stageId) {
                            $assignedStageQuery->whereDoesntHave('stageApprovals', function ($approvalQuery) use ($stageId) {
                                $approvalQuery->where('pipeline_stage_id', $stageId);
                            });
                        });
                    }
                });
        } elseif ($tab === 'completed') {
            $query->where('approval_status', 'completed');
        } elseif ($tab === 'rejected') {
            $query->where('approval_status', 'rejected');
        } elseif ($tab) {
            $selectedStage = PipelineStage::find($tab);
            if ($selectedStage && $selectedStage->partner_id === $partnerId) {
                $isAssignedToThisStage = $selectedStage->assigned_to === $user->id || in_array((string) $selectedStage->id, $myStageIds, true);
                if ($isAssignedToThisStage) {
                    $query->where(function ($q) use ($tab) {
                        $q->where('current_stage_id', $tab)->orWhere('approval_status', 'pending');
                    });
                } else {
                    $query->where('current_stage_id', $tab)->where('approval_status', 'pending');
                }
            }
        }

        $perPage = max(1, (int) $request->input('per_page', 10));
        $orders = $query->latest()->paginate($perPage);

        $data = collect($orders->items())->map(fn ($order) => $this->presentOrder($order))->values();

        $pipelineStages = PipelineStage::where('partner_id', $partnerId)->orderBy('order_index')->get(['id', 'name']);

        $tabs = [['key' => 'all', 'label' => 'All Orders']];
        if (!empty($myStageIds)) {
            $tabs[] = ['key' => 'action_required', 'label' => 'Requires My Action'];
        }
        foreach ($pipelineStages as $stageItem) {
            $tabs[] = ['key' => $stageItem->id, 'label' => $stageItem->name];
        }
        $tabs[] = ['key' => 'completed', 'label' => 'Completed'];
        $tabs[] = ['key' => 'rejected', 'label' => 'Rejected'];

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'active_tab' => $tab,
                'tabs' => $tabs,
            ],
        ]);
    }

    public function getOrderTabs(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $myStageIds = $this->myStageIdsFor($user, $partnerId);

        $base = LeadOrder::query()
            ->where('partner_id', $partnerId);

        if (!$user->isPartner() && !$user->canAccess('leadorder_viewAny')) {
            $base->where(function ($q) use ($user, $myStageIds) {
                if ($user->canAccess('leadorder_viewBranch') || $user->canAccess('leadorder_viewTeam')) {
                    $q->whereIn('employee_id', $user->getTeamIds());
                } else {
                    $q->where('employee_id', $user->id);
                }
                if (!empty($myStageIds)) {
                    $q->orWhere('approval_status', 'pending');
                }
            });
        }

        $pipelineStages = PipelineStage::where('partner_id', $partnerId)->orderBy('order_index')->get(['id', 'name']);

        $tabs = [];

        $allCount = (clone $base)->count();
        $tabs[] = ['key' => 'all', 'label' => 'All Orders', 'count' => $allCount];

        if (!empty($myStageIds)) {
            $actionCount = (clone $base)->where('approval_status', 'pending')
                ->where(function ($stageQuery) use ($myStageIds) {
                    foreach ($myStageIds as $stageId) {
                        $stageQuery->orWhere(function ($assignedStageQuery) use ($stageId) {
                            $assignedStageQuery->whereDoesntHave('stageApprovals', function ($approvalQuery) use ($stageId) {
                                $approvalQuery->where('pipeline_stage_id', $stageId);
                            });
                        });
                    }
                })
                ->count();
            $tabs[] = ['key' => 'action_required', 'label' => 'Requires My Action', 'count' => $actionCount];
        }

        foreach ($pipelineStages as $stageItem) {
            $count = (clone $base)
                ->where(function ($q) use ($stageItem, $user, $myStageIds) {
                    $isAssigned = $stageItem->assigned_to === $user->id || in_array((string) $stageItem->id, $myStageIds, true);
                    if ($isAssigned) {
                        $q->where('current_stage_id', $stageItem->id)->orWhere('approval_status', 'pending');
                    } else {
                        $q->where('current_stage_id', $stageItem->id)->where('approval_status', 'pending');
                    }
                })
                ->count();
            $tabs[] = ['key' => $stageItem->id, 'label' => $stageItem->name, 'count' => $count];
        }

        $tabs[] = ['key' => 'completed', 'label' => 'Completed', 'count' => (clone $base)->where('approval_status', 'completed')->count()];
        $tabs[] = ['key' => 'rejected', 'label' => 'Rejected', 'count' => (clone $base)->where('approval_status', 'rejected')->count()];

        return response()->json([
            'status' => 'success',
            'data' => $tabs,
        ]);
    }

    /**
     * Get single lead order with full details (mirrors web showOrder modal)
     */
    public function showOrder($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $order = LeadOrder::with([
            'lead', 'employee', 'items.product', 'currentStage.assignedUser',
            'currentStage.department', 'stageComments.user', 'stageComments.stage',
        ])
            ->where('partner_id', $partnerId)
            ->find($id);

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found.'], 404);
        }

        $myStageIds = $this->myStageIdsFor($user, $partnerId);

        if (!$user->isPartner() && !$user->canAccess('leadorder_viewAny')) {
            $isCreator = $order->employee_id === $user->id;
            $isTeam = ($user->canAccess('leadorder_viewBranch') || $user->canAccess('leadorder_viewTeam')) && in_array($order->employee_id, $user->getTeamIds());
            $isStageApprover = !empty($myStageIds);

            if (!$isCreator && !$isTeam && !$isStageApprover) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->presentOrder($order, true, $myStageIds)
        ]);
    }

    /**
     * Add a comment to the current action stage of an order (mirrors web saveComment)
     */
    public function addStageComment(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $order = LeadOrder::where('partner_id', $partnerId)->find($id);

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found.'], 404);
        }

        $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $myStageIds = $this->myStageIdsFor($user, $partnerId);
        $stageId = $this->actionStageIdFor($order, $myStageIds);

        if (!$stageId || !in_array((string) $stageId, $myStageIds, true)) {
            return response()->json(['status' => 'error', 'message' => 'You do not have permission to comment on this order.'], 403);
        }

        $comment = LeadOrderStageComment::create([
            'lead_order_id'     => $order->id,
            'pipeline_stage_id' => $stageId,
            'user_id'           => $user->id,
            'comment'           => $request->comment,
        ]);

        $comment->load(['user:id,name', 'stage:id,name']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Comment added successfully.',
            'data'    => $comment
        ], 201);
    }

    /**
     * Pipeline stage IDs the user can act on (mirrors web loadStages -> myStageIds)
     */
    private function myStageIdsFor($user, $partnerId): array
    {
        $stages = PipelineStage::where('partner_id', $partnerId)->orderBy('order_index')->get(['id', 'assigned_to', 'department_id']);

        if ($user->isPartner()) {
            return $stages->pluck('id')->map(fn ($id) => (string) $id)->values()->all();
        }

        $departmentId = $user->department_id;
        $headedDepts = \App\Models\DepartmentBranchHead::where('head_id', $user->id)->pluck('department_id')->toArray();
        $myDepts = array_unique(array_filter(array_merge([$departmentId], $headedDepts)));
        $userId = (string) $user->id;

        return $stages->filter(function ($stage) use ($userId, $myDepts, $headedDepts) {
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

    /**
     * First pending stage of the order the current user still needs to action (mirrors web actionStageFor)
     */
    private function actionStageIdFor(LeadOrder $order, array $myStageIds): ?string
    {
        if ($order->approval_status !== 'pending') {
            return null;
        }

        $approvedStageIds = $order->stageApprovals()
            ->where('status', 'approved')
            ->pluck('pipeline_stage_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return collect($myStageIds)->first(fn ($stageId) => !in_array((string) $stageId, $approvedStageIds, true));
    }

    /**
     * Present an order in the shape of the web Lead Orders Pipeline page
     */
    private function presentOrder(LeadOrder $order, bool $withDetails = false, array $myStageIds = []): array
    {
        $data = [
            'id'                => $order->id,
            'order_number'      => '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
            'lead'              => $order->lead ? [
                'id'             => $order->lead->id,
                'customer_name'  => $order->lead->customer_name,
                'customer_mobile' => $order->lead->customer_mobile,
            ] : null,
            'employee'          => $order->employee ? [
                'id'   => $order->employee->id,
                'name' => $order->employee->name,
            ] : null,
            'items_count'       => $order->items->count(),
            'base_amount'       => (float) $order->base_amount,
            'gst_type'          => $order->gst_type,
            'gst_percent'       => (float) $order->gst_percent,
            'gst_amount'        => (float) $order->gst_amount,
            'total_amount'      => (float) $order->total_amount,
            'discount'          => (float) $order->discount,
            'final_amount'      => (float) $order->final_amount,
            'paid_amount'       => (float) $order->paid_amount,
            'remaining_balance' => (float) $order->remaining_balance,
            'approval_status'   => $order->approval_status,
            'payment_status'    => $order->payment_status,
            'target_credited'   => (bool) $order->target_credited,
            'current_stage'     => $order->currentStage ? [
                'id'            => $order->currentStage->id,
                'name'          => $order->currentStage->name,
                'assigned_user' => $order->currentStage->assignedUser ? $order->currentStage->assignedUser->name : null,
                'department'    => $order->currentStage->department ? $order->currentStage->department->name : null,
            ] : null,
            'created_at'        => $order->created_at ? $order->created_at->format('M d, Y') : null,
        ];

        if ($withDetails) {
            $data['items'] = $order->items->map(fn ($item) => [
                'id'          => $item->id,
                'product_id'  => $item->product_id,
                'product_name' => $item->product->name ?? 'Unknown Product',
                'base_price'  => (float) ($item->base_price ?: $item->price),
                'gst_type'    => $item->gst_type,
                'gst_percent' => (float) $item->gst_percent,
                'unit_price'  => (float) $item->price,
                'quantity'    => (int) $item->quantity,
                'subtotal'    => round((float) $item->price * (int) $item->quantity, 2),
            ])->values();

            $data['stage_comments'] = $order->stageComments->map(fn ($c) => [
                'id'         => $c->id,
                'user_name'  => $c->user->name ?? null,
                'stage_name' => $c->stage->name ?? null,
                'comment'    => $c->comment,
                'created_at' => $c->created_at ? $c->created_at->format('d M Y, h:i A') : null,
            ])->values();

            $data['can_comment'] = (bool) $this->actionStageIdFor($order, $myStageIds);

            $data['pipeline_stages'] = PipelineStage::where('partner_id', $order->partner_id)
                ->with('assignedUser:id,name')
                ->orderBy('order_index')
                ->get()
                ->map(function ($stage) use ($order) {
                    $isCurrent = $order->current_stage_id === $stage->id && $order->approval_status === 'pending';
                    $isPassed = $order->approval_status === 'completed' || ($order->currentStage && $stage->order_index < $order->currentStage->order_index);

                    return [
                        'id'            => $stage->id,
                        'name'          => $stage->name,
                        'order_index'   => $stage->order_index,
                        'assigned_user' => $stage->assignedUser->name ?? null,
                        'is_current'    => (bool) $isCurrent,
                        'is_passed'     => (bool) $isPassed,
                    ];
                })->values();
        }

        return $data;
    }


    /**
     * Create Lead Order (mirrors web "Create New Lead Order" modal)
     * Supports either the web single-product payload
     *   { lead_id, product_id, amount, quantity, discount, paid_amount }
     * or the legacy multi-item payload { lead_id, discount, paid_amount, items[] }.
     */
    /**
     * Calculate order totals (mirrors web LeadOrders getCalculatedTotalsProperty)
     */
    public function calculateOrder(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'discount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $gstType = 'notinclude';
        $gstPercent = 0;
        $unitPrice = max(0, (float) ($request->amount ?? 0));
        $product = null;

        if ($request->filled('product_id')) {
            $product = \App\Models\Product::find($request->product_id);
            $gstType = $product && $product->gst_type ? $product->gst_type : 'notinclude';
            $gstPercent = $gstType === 'include' ? (float) ($product->gst_percent ?: 0) : 0;
            if ($unitPrice <= 0) {
                $unitPrice = max(0, (float) ($product->amount ?: 0));
            }
        }

        $baseUnitPrice = $unitPrice;
        $qty = max(1, (int) ($request->quantity ?? 1));
        $baseSubtotal = round($baseUnitPrice * $qty, 2);

        $isGstIncluded = $gstType === 'include' && (float) $gstPercent > 0;
        $gstRate = $isGstIncluded ? (float) $gstPercent : 0;
        $gstAmount = $isGstIncluded ? round(($baseSubtotal * $gstRate) / 100, 2) : 0;
        $unitGst = $isGstIncluded ? round(($baseUnitPrice * $gstRate) / 100, 2) : 0;
        $unitTotalWithGst = round($baseUnitPrice + $unitGst, 2);
        $grossTotal = round($baseSubtotal + $gstAmount, 2);
        $discountAmount = max(0, (float) ($request->discount ?? 0));
        $finalAmount = max(0, round($grossTotal - $discountAmount, 2));
        $paidAmount = max(0, (float) ($request->paid_amount ?? 0));
        $remainingBalance = max(0, round($finalAmount - $paidAmount, 2));

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => $product ? [
                    'id'          => $product->id,
                    'name'        => $product->name,
                    'amount'      => (float) $product->amount,
                    'gst_type'    => $gstType,
                    'gst_percent' => (float) $gstPercent,
                ] : null,
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
            ],
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        if (!$user->isPartner() && !$user->canAccess('leadorder_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'product_id' => 'required_without:items|exists:products,id',
            'amount' => 'required_with:product_id|numeric|min:0',
            'quantity' => 'nullable|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'items' => 'required_without:product_id|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        // Verify the user has access to the selected lead (mirrors web createOrder)
        if (!$user->isPartner() && !$user->canAccess('lead_viewAny')) {
            $lead = Lead::findOrFail($request->lead_id);
            if ($user->canAccess('lead_viewBranch') || $user->canAccess('lead_viewTeam')) {
                if (!in_array($lead->assigned_to, $user->getTeamIds())) {
                    return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
                }
            } else if ($lead->assigned_to !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }
        }

        DB::beginTransaction();
        try {
            $orderItems = [];
            $baseSubtotal = 0;
            $gstAmount = 0;
            $totalAmount = 0;

            // Web single-product payload: GST strictly synced from the selected product (like the modal)
            if ($request->filled('product_id')) {
                $product = \App\Models\Product::findOrFail($request->product_id);
                $gstType = $product->gst_type ?: 'notinclude';
                $gstPercent = $gstType === 'include' ? (float) ($product->gst_percent ?: 0) : 0;
                $unitPrice = max(0, (float) $request->amount);
                $qty = max(1, (int) ($request->quantity ?? 1));

                $baseSubtotal = round($unitPrice * $qty, 2);
                $baseGst = $gstType === 'include' ? round($baseSubtotal * $gstPercent / 100, 2) : 0;
                $unitGst = $gstType === 'include' ? round($unitPrice * $gstPercent / 100, 2) : 0;
                $unitTotal = round($unitPrice + $unitGst, 2);
                $totalAmount = round($baseSubtotal + $baseGst, 2);

                $orderItems[] = [
                    'product_id'  => $product->id,
                    'base_price'  => $unitPrice,
                    'gst_type'    => $gstType,
                    'gst_percent' => $gstPercent,
                    'gst_amount'  => $unitGst,
                    'price'       => $unitTotal,
                    'quantity'    => $qty,
                ];

                $gstAmount = $baseGst;
            } else {
                // Legacy multi-item payload
                $orderGstType = $request->gst_type ?? 'notinclude';
                $orderGstPercent = (float) ($request->gst_percent ?? 0);

                foreach ($request->items as $item) {
                    $itemBase = (float) ($item['base_price'] ?? $item['price']);
                    $qty = (int) $item['quantity'];
                    $itemGstType = $item['gst_type'] ?? $orderGstType;
                    $itemGstPercent = (float) ($item['gst_percent'] ?? $orderGstPercent);
                    $itemUnitGst = $itemGstType === 'include' ? round(($itemBase * $itemGstPercent) / 100, 2) : 0;

                    $itemGstAmount = $itemGstType === 'include' ? round(($itemBase * $qty * $itemGstPercent) / 100, 2) : 0;
                    $itemTotal = ($itemBase * $qty) + $itemGstAmount;

                    $baseSubtotal += ($itemBase * $qty);
                    $gstAmount += $itemGstAmount;
                    $totalAmount += $itemTotal;

                    $orderItems[] = [
                        'product_id'  => $item['product_id'],
                        'base_price'  => $itemBase,
                        'gst_type'    => $itemGstType,
                        'gst_percent' => $itemGstPercent,
                        'gst_amount'  => $itemUnitGst,
                        'price'       => $itemBase + $itemUnitGst,
                        'quantity'    => $qty,
                    ];
                }
            }

            $gstType = $request->filled('product_id') ? ($product->gst_type ?: 'notinclude') : ($request->gst_type ?? 'notinclude');
            $gstPercent = $request->filled('product_id') ? ($gstType === 'include' ? (float) ($product->gst_percent ?: 0) : 0) : (float) ($request->gst_percent ?? 0);

            $discountAmount = max(0, (float) ($request->discount ?? 0));
            $finalAmount = max(0, round($totalAmount - $discountAmount, 2));
            $paidAmount = max(0, (float) ($request->paid_amount ?? 0));
            $remainingBalance = max(0, round($finalAmount - $paidAmount, 2));

            // Get first stage
            $firstStage = \App\Models\PipelineStage::where('partner_id', $partnerId)->orderBy('order_index')->first();

            $order = LeadOrder::create([
                'lead_id'           => $request->lead_id,
                'base_amount'       => $baseSubtotal,
                'gst_type'          => $gstType,
                'gst_percent'       => $gstPercent,
                'gst_amount'        => $gstAmount,
                'total_amount'      => $totalAmount,
                'discount'          => $discountAmount,
                'final_amount'      => $finalAmount,
                'paid_amount'       => $paidAmount,
                'remaining_balance' => $remainingBalance,
                'approval_status'   => 'pending',
                'current_stage_id'  => $firstStage ? $firstStage->id : null,
                'payment_status'    => $remainingBalance > 0 ? ($paidAmount > 0 ? 'partial' : 'pending') : 'paid',
                'partner_id'        => $partnerId,
                'employee_id'       => $user->id,
            ]);

            // If there are no stages, mark it completed immediately
            if (!$firstStage) {
                $order->approval_status = 'completed';
                $order->target_credited = true;
                $order->save();
            }

            foreach ($orderItems as $item) {
                LeadOrderItem::create([
                    'lead_order_id' => $order->id,
                    'product_id'    => $item['product_id'],
                    'base_price'    => $item['base_price'],
                    'gst_type'      => $item['gst_type'],
                    'gst_percent'   => $item['gst_percent'],
                    'gst_amount'    => $item['gst_amount'],
                    'quantity'      => $item['quantity'],
                    'price'         => $item['price'],
                ]);
            }

            DB::commit();

            $order->load(['lead', 'employee', 'items.product', 'currentStage.assignedUser', 'currentStage.department']);

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully.',
                'data' => $this->presentOrder($order, true, $this->myStageIdsFor($user, $partnerId)),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to create order.'], 500);
        }
    }

    public function updateOrder(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $order = LeadOrder::where('partner_id', $user->partner_id ?? $user->id)->findOrFail($id);

        if (!$user->isPartner() && !$user->canAccess('leadorder_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += ($item['quantity'] * $item['price']);
            }
            
            $discountAmount = (float)($request->discount ?? 0);
            $finalAmount = max(0, $subtotal - $discountAmount);
            $paidAmount = (float)($request->paid_amount ?? 0);
            $remainingBalance = max(0, $finalAmount - $paidAmount);

            $order->update([
                'total_amount' => $subtotal,
                'discount' => $discountAmount,
                'final_amount' => $finalAmount,
                'paid_amount' => $paidAmount,
                'remaining_balance' => $remainingBalance,
                'payment_status' => $remainingBalance > 0 ? 'partial' : 'paid',
            ]);

            // Simple replace items strategy
            $order->items()->delete();

            foreach ($request->items as $item) {
                LeadOrderItem::create([
                    'lead_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'data' => $order->load('items')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to update order.'], 500);
        }
    }

    public function deleteOrder($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $order = LeadOrder::where('partner_id', $partnerId)->findOrFail($id);

        if (!$user->isPartner() && !$user->canAccess('leadorder_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $order->delete(); // Assuming items delete via cascade or model events

        return response()->json(['status' => 'success', 'message' => 'Order deleted successfully.']);
    }

    public function approveOrder(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $order = LeadOrder::where('partner_id', $partnerId)->findOrFail($id);

        $myStageIds = $this->myStageIdsFor($user, $partnerId);
        $hasPermission = $user->isPartner()
            || $user->canAccess('leadorder_approve')
            || in_array((string) $order->current_stage_id, $myStageIds, true)
            || !empty($myStageIds);

        if (!$hasPermission) {
            return response()->json(['status' => 'error', 'message' => 'You do not have permission to approve this order.'], 403);
        }

        try {
            $pipeline = new OrderPipelineService();
            $pipeline->approveOrder($order, $user->id);

            $order->load(['lead', 'employee', 'items.product', 'currentStage.assignedUser', 'currentStage.department']);

            return response()->json([
                'status'  => 'success',
                'message' => 'Order approved successfully.',
                'data'    => $this->presentOrder($order, true, $myStageIds),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function rejectOrder(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $order = LeadOrder::where('partner_id', $partnerId)->findOrFail($id);

        $request->validate([
            'reason' => 'nullable|string|max:2000',
        ]);

        $myStageIds = $this->myStageIdsFor($user, $partnerId);
        $stageId = $this->actionStageIdFor($order, $myStageIds);

        if (!$stageId || !in_array((string) $stageId, $myStageIds, true)) {
            return response()->json(['status' => 'error', 'message' => 'You do not have permission to reject this order.'], 403);
        }

        DB::beginTransaction();
        try {
            LeadOrderStageApproval::updateOrCreate(
                ['lead_order_id' => $order->id, 'pipeline_stage_id' => $stageId],
                ['approved_by' => $user->id, 'status' => 'rejected', 'notes' => $request->reason, 'approved_at' => now()]
            );
            $order->update(['approval_status' => 'rejected']);

            DB::commit();

            $order->load(['lead', 'employee', 'items.product', 'currentStage.assignedUser', 'currentStage.department']);

            return response()->json([
                'status'  => 'success',
                'message' => 'Order rejected.',
                'data'    => $this->presentOrder($order, true, $myStageIds),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to reject order.'], 500);
        }
    }





public function newLeadOrder()
{
    try {
        $user = auth()->user();

        $leadsQuery = Lead::where('partner_id', $user->id)
            ->where('status', 'won');

        if (!$user->isPartner() && !$user->canAccess('lead_viewAny')) {

            if (
                $user->canAccess('lead_viewBranch') ||
                $user->canAccess('lead_viewTeam')
            ) {
                $leadsQuery->whereIn(
                    'assigned_to',
                    $user->getTeamIds()
                );
            } else {
                // Only own assigned leads
                $leadsQuery->where('assigned_to', $user->id);
            }
        }

        $leads = $leadsQuery
            ->latest()
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Lead order won list.',
            'data'    => $leads,
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to get won leads.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

}
