<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Notice;
use Carbon\Carbon;

class NoticeController extends Controller
{
    /**
     * Get active notices for the employee
     */
    public function index_old(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $notices = Notice::where(function ($q) use ($partnerId) {
            $q->whereNull('user_id')
              ->orWhereHas('user', function ($q2) use ($partnerId) {
                  $q2->where('id', $partnerId)->orWhere('parent_id', $partnerId);
              });
        })->where(function ($q) use ($user) {
            // Global notices are visible to everyone
            $q->where('type', 'global')
              // The creator of the notice can always see it
              ->orWhere('user_id', $user->id)
              // Targeted employee notices
              ->orWhere(function ($subQ) use ($user) {
                  $subQ->whereIn('type', ['personal', 'employee'])
                       ->where(function($pQ) use ($user) {
                           $pQ->whereJsonContains('user_ids', (string)$user->id)
                              ->orWhereJsonContains('user_ids', (int)$user->id);
                       });
              });
                
            // Targeted branch notices
            if ($user->branch_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('type', 'branch')
                         ->where(function($pQ) use ($user) {
                             $pQ->whereJsonContains('branch_ids', (string)$user->branch_id)
                                ->orWhereJsonContains('branch_ids', (int)$user->branch_id);
                         });
                });
            }
            
            // Targeted department notices
            if ($user->department_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('type', 'department')
                         ->where(function($pQ) use ($user) {
                             $pQ->whereJsonContains('department_ids', (string)$user->department_id)
                                ->orWhereJsonContains('department_ids', (int)$user->department_id);
                         });
                });
            }
        })->where(function ($q) {
            $q->whereNull('start_date')
                ->orWhere(function ($subQ) {
                    $subQ->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today());
                });
        })->orderBy('created_at', 'desc')->get();

        $message = $notices->isEmpty() ? 'No active notices found.' : 'Notices fetched successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $notices
        ]);
    }
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $notices = Notice::where(function ($q) use ($user) {
            // Global notices are visible to everyone
            $q->where('type', 'global')
              // The creator of the notice can always see it
              ->orWhere('user_id', $user->id)
              // Targeted employee notices
              ->orWhere(function ($subQ) use ($user) {
                  $subQ->whereIn('type', ['personal', 'employee'])
                       ->where(function($pQ) use ($user) {
                           $pQ->whereJsonContains('user_ids', (string)$user->id)
                              ->orWhereJsonContains('user_ids', (int)$user->id);
                       });
              });
                
            // Targeted branch notices
            if ($user->branch_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('type', 'branch')
                         ->where(function($pQ) use ($user) {
                             $pQ->whereJsonContains('branch_ids', (string)$user->branch_id)
                                ->orWhereJsonContains('branch_ids', (int)$user->branch_id);
                         });
                });
            }
            
            // Targeted department notices
            if ($user->department_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('type', 'department')
                         ->where(function($pQ) use ($user) {
                             $pQ->whereJsonContains('department_ids', (string)$user->department_id)
                                ->orWhereJsonContains('department_ids', (int)$user->department_id);
                         });
                });
            }
        })->where(function ($q) {
            $q->whereNull('start_date')
                ->orWhere(function ($subQ) {
                    $subQ->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today());
                });
        })->orderBy('created_at', 'desc')->get();

        $message = $notices->isEmpty() ? 'No active notices found.' : 'Notices fetched successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $notices
        ]);
    }

    /**
     * Get details of a specific notice
     */
    public function show($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $notice = Notice::where(function ($q) use ($partnerId) {
            $q->whereNull('user_id')
              ->orWhereHas('user', function ($q2) use ($partnerId) {
                  $q2->where('id', $partnerId)->orWhere('parent_id', $partnerId);
              });
        })->where(function ($q) use ($user) {
            $q->where('type', 'global')
              ->orWhere('user_id', $user->id)
              ->orWhere(function ($subQ) use ($user) {
                  $subQ->whereIn('type', ['personal', 'employee'])
                       ->where(function($pQ) use ($user) {
                           $pQ->whereJsonContains('user_ids', (string)$user->id)
                              ->orWhereJsonContains('user_ids', (int)$user->id);
                       });
              });
                
            if ($user->branch_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('type', 'branch')
                         ->where(function($pQ) use ($user) {
                             $pQ->whereJsonContains('branch_ids', (string)$user->branch_id)
                                ->orWhereJsonContains('branch_ids', (int)$user->branch_id);
                         });
                });
            }
            
            if ($user->department_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('type', 'department')
                         ->where(function($pQ) use ($user) {
                             $pQ->whereJsonContains('department_ids', (string)$user->department_id)
                                ->orWhereJsonContains('department_ids', (int)$user->department_id);
                         });
                });
            }
        })->where('id', $id)->first();

        if (!$notice) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notice not found or unauthorized.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notice details fetched successfully.',
            'data' => $notice
        ]);
    }

    /**
     * Create a new notice
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('notice_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:global,personal,branch,department,employee',
            'user_ids' => 'required_if:type,personal,employee|nullable|array',
            'branch_ids' => 'required_if:type,branch|nullable|array',
            'department_ids' => 'required_if:type,department|nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'action_link' => 'nullable|url|max:2000',
            'action_text' => 'nullable|string|max:100',
        ]);

        $type = $request->type === 'personal' ? 'employee' : $request->type;
        $selectedUserIds = in_array($type, ['employee']) ? array_values(array_unique((array)$request->user_ids)) : null;
        $selectedBranchIds = $type === 'branch' ? array_values(array_unique((array)$request->branch_ids)) : null;
        $selectedDepartmentIds = $type === 'department' ? array_values(array_unique((array)$request->department_ids)) : null;
        
        $primaryUserId = $selectedUserIds && count($selectedUserIds) > 0 ? $selectedUserIds[0] : $user->id;

        $notice = Notice::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $type,
            'user_id' => $primaryUserId,
            'user_ids' => $selectedUserIds,
            'branch_ids' => $selectedBranchIds,
            'department_ids' => $selectedDepartmentIds,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'action_link' => $request->action_link,
            'action_text' => $request->action_text ?: 'Join Now',
        ]);

        // Option: Send push notifications here if desired, similar to Web HRMS
        
        return response()->json(['status' => 'success', 'message' => 'Notice created successfully.', 'data' => $notice], 201);
    }

    /**
     * Update an existing notice
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('notice_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        
        $notice = Notice::where('id', $id)->where(function ($q) use ($partnerId) {
            $q->whereNull('user_id')
              ->orWhereHas('user', function ($q2) use ($partnerId) {
                  $q2->where('id', $partnerId)->orWhere('parent_id', $partnerId);
              });
        })->first();

        if (!$notice) {
            return response()->json(['status' => 'error', 'message' => 'Notice not found or unauthorized.'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:global,personal,branch,department,employee',
            'user_ids' => 'required_if:type,personal,employee|nullable|array',
            'branch_ids' => 'required_if:type,branch|nullable|array',
            'department_ids' => 'required_if:type,department|nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'action_link' => 'nullable|url|max:2000',
            'action_text' => 'nullable|string|max:100',
        ]);

        $type = $request->type === 'personal' ? 'employee' : $request->type;
        $selectedUserIds = in_array($type, ['employee']) ? array_values(array_unique((array)$request->user_ids)) : null;
        $selectedBranchIds = $type === 'branch' ? array_values(array_unique((array)$request->branch_ids)) : null;
        $selectedDepartmentIds = $type === 'department' ? array_values(array_unique((array)$request->department_ids)) : null;
        
        $primaryUserId = $selectedUserIds && count($selectedUserIds) > 0 ? $selectedUserIds[0] : $user->id;

        $notice->update([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $type,
            'user_id' => $primaryUserId,
            'user_ids' => $selectedUserIds,
            'branch_ids' => $selectedBranchIds,
            'department_ids' => $selectedDepartmentIds,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'action_link' => $request->action_link,
            'action_text' => $request->action_text ?: 'Join Now',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Notice updated successfully.', 'data' => $notice]);
    }

    /**
     * Delete a notice
     */
    public function destroy($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('notice_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        
        $notice = Notice::where('id', $id)->where(function ($q) use ($partnerId) {
            $q->whereNull('user_id')
              ->orWhereHas('user', function ($q2) use ($partnerId) {
                  $q2->where('id', $partnerId)->orWhere('parent_id', $partnerId);
              });
        })->first();

        if (!$notice) {
            return response()->json(['status' => 'error', 'message' => 'Notice not found or unauthorized.'], 404);
        }

        $notice->delete();

        return response()->json(['status' => 'success', 'message' => 'Notice deleted successfully.']);
    }
}

