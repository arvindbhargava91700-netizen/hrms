<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ProcessNote;

class ProcessNoteController extends Controller
{
    /**
     * Get Process Notes
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $query = ProcessNote::where('partner_id', $partnerId)
                            ->where('status', 'active');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $notes = $query->orderBy('created_at', 'desc')->get();

        $notes->transform(function ($note) {
            return [
                'id' => $note->id,
                'title' => $note->title,
                'description' => $note->description,
                'status' => $note->status,
                'created_at' => $note->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $notes,
        ]);
    }
}
