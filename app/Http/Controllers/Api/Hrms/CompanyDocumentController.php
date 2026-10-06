<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\CompanyDocument;

class CompanyDocumentController extends Controller
{
    /**
     * Get Company Documents
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $query = CompanyDocument::where('partner_id', $partnerId)
                                ->where('status', 'active');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $documents = $query->orderBy('created_at', 'desc')->get();

        $documents->transform(function ($doc) {
            $fileUrls = [];
            if (is_array($doc->files)) {
                foreach ($doc->files as $file) {
                    $fileUrls[] = [
                        'url' => asset('storage/' . $file),
                        'type' => pathinfo($file, PATHINFO_EXTENSION),
                        'size' => null,
                    ];
                }
            }

            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'files' => $fileUrls,
                'created_at' => $doc->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $documents,
        ]);
    }
}
