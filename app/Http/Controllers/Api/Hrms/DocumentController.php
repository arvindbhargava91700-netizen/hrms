<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Get Employee Documents
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $query = EmployeeDocument::where('partner_id', $partnerId)->with(['employee', 'creator']);

        if ($user->role === 'employee' && !$user->canAccess('document_viewAny')) {
            $allowedIds = $user->getTeamIds();
            $query->where(function ($q) use ($allowedIds, $user) {
                $q->whereIn('employee_id', $allowedIds)
                  ->orWhereIn('created_by', $allowedIds)
                  ->orWhere('employee_id', $user->id);
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('document_category')) {
            $query->where('document_category', $request->document_category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('expiring_soon')) {
            $query->whereNotNull('expiry_date')
                  ->where('expiry_date', '<=', now()->addDays(30));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(15);

        // Map full file URL
        $documents->getCollection()->transform(function ($doc) {
            $doc->file_url = $doc->file_path ? asset('storage/' . $doc->file_path) : null;
            return $doc;
        });

        return response()->json([
            'status'  => 'success',
            'message' => $documents->isEmpty() ? 'No document records found.' : 'Documents fetched successfully.',
            'data'    => $documents
        ]);
    }

    /**
     * Show single document detail
     */
    public function show($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $doc = EmployeeDocument::where('partner_id', $partnerId)->with(['employee', 'creator'])->findOrFail($id);
        $doc->file_url = $doc->file_path ? asset('storage/' . $doc->file_path) : null;

        return response()->json([
            'status'  => 'success',
            'message' => 'Document details fetched successfully.',
            'data'    => $doc
        ]);
    }

    /**
     * Store new document with file upload
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $request->validate([
            'employee_id'       => 'required|exists:users,id',
            'document_category' => 'required|in:kyc,offer_letter,appointment_letter,agreement,other',
            'document_type'     => 'required|in:aadhaar,pan,bank_passbook,driving_license,passport,voter_id,offer_letter,appointment_letter,nda,service_agreement,other',
            'title'             => 'required|string|max:255',
            'document_number'   => 'nullable|string|max:100',
            'file'              => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'issue_date'        => 'nullable|date',
            'expiry_date'       => 'nullable|date|after_or_equal:issue_date',
            'status'            => 'required|in:pending_verification,verified,rejected,expired',
            'remarks'           => 'nullable|string',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('employee_documents', 'public');
        }

        $doc = EmployeeDocument::create([
            'partner_id'        => $partnerId,
            'created_by'        => $user->id,
            'employee_id'       => $request->employee_id,
            'document_category' => $request->document_category,
            'document_type'     => $request->document_type,
            'title'             => $request->title,
            'document_number'   => $request->document_number,
            'file_path'         => $filePath,
            'issue_date'        => $request->issue_date ?: null,
            'expiry_date'       => $request->expiry_date ?: null,
            'status'            => $request->status,
            'remarks'           => $request->remarks,
        ]);

        $doc->file_url = $doc->file_path ? asset('storage/' . $doc->file_path) : null;

        return response()->json([
            'status'  => 'success',
            'message' => 'Document uploaded & created successfully.',
            'data'    => $doc
        ], 201);
    }

    /**
     * Update document
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $doc = EmployeeDocument::where('partner_id', $partnerId)->findOrFail($id);

        $request->validate([
            'employee_id'       => 'sometimes|required|exists:users,id',
            'document_category' => 'sometimes|required|in:kyc,offer_letter,appointment_letter,agreement,other',
            'document_type'     => 'sometimes|required|in:aadhaar,pan,bank_passbook,driving_license,passport,voter_id,offer_letter,appointment_letter,nda,service_agreement,other',
            'title'             => 'sometimes|required|string|max:255',
            'document_number'   => 'nullable|string|max:100',
            'file'              => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'issue_date'        => 'nullable|date',
            'expiry_date'       => 'nullable|date|after_or_equal:issue_date',
            'status'            => 'sometimes|required|in:pending_verification,verified,rejected,expired',
            'remarks'           => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }
            $doc->file_path = $request->file('file')->store('employee_documents', 'public');
        }

        $doc->update($request->only([
            'employee_id', 'document_category', 'document_type', 'title',
            'document_number', 'issue_date', 'expiry_date', 'status', 'remarks'
        ]));

        $doc->file_url = $doc->file_path ? asset('storage/' . $doc->file_path) : null;

        return response()->json([
            'status'  => 'success',
            'message' => 'Document updated successfully.',
            'data'    => $doc
        ]);
    }

    /**
     * Delete document
     */
    public function destroy($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $doc = EmployeeDocument::where('partner_id', $partnerId)->findOrFail($id);

        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $doc->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Document deleted successfully.'
        ]);
    }
}
