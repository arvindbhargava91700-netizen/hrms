<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Asset;
use App\Models\HrmsBranch;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    /**
     * Helper to get partner_id for HRMS API authenticated user
     */
    private function getPartnerId()
    {
        $user = auth('hrms_api')->user();
        return $user->isPartner() ? $user->id : $user->parent_id;
    }

    /**
     * Get Assets Listing with Analytics & KPI Metrics
     */
    public function index(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $category = $request->input('category', 'all');
        $status = $request->input('status', 'all');
        $condition = $request->input('condition', 'all');
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        $assetsQuery = Asset::where('partner_id', $partnerId);

        if ($category !== 'all' && !empty($category)) {
            $assetsQuery->where('category', $category);
        }
        if ($status !== 'all' && !empty($status)) {
            $assetsQuery->where('status', $status);
        }
        if ($condition !== 'all' && !empty($condition)) {
            $assetsQuery->where('condition', $condition);
        }
        if (!empty($branchId)) {
            $assetsQuery->where('branch_id', $branchId);
        }
        if (!empty($departmentId)) {
            $assetsQuery->where('department_id', $departmentId);
        }
        if (!empty($search)) {
            $s = '%' . $search . '%';
            $assetsQuery->where(function ($q) use ($s) {
                $q->where('asset_code', 'like', $s)
                    ->orWhere('name', 'like', $s)
                    ->orWhere('brand', 'like', $s)
                    ->orWhere('model', 'like', $s)
                    ->orWhere('serial_number', 'like', $s)
                    ->orWhere('imei_number', 'like', $s)
                    ->orWhere('mobile_number', 'like', $s)
                    ->orWhere('sim_number', 'like', $s);
            });
        }

        $allFilteredAssets = (clone $assetsQuery)->get();

        // Key KPI Statistics
        $totalAssets = $allFilteredAssets->count();
        $availableAssets = $allFilteredAssets->where('status', 'available')->count();
        $issuedAssets = $allFilteredAssets->where('status', 'issued')->count();
        $damagedAssets = $allFilteredAssets->where('status', 'damaged')->count();
        $totalAssetValue = round($allFilteredAssets->sum('purchase_cost'), 2);

        // Category Breakdown
        $categoryBreakdown = [
            ['label' => 'Laptop', 'category' => 'laptop', 'total' => $allFilteredAssets->where('category', 'laptop')->count()],
            ['label' => 'Mobile', 'category' => 'mobile', 'total' => $allFilteredAssets->where('category', 'mobile')->count()],
            ['label' => 'SIM Card', 'category' => 'sim', 'total' => $allFilteredAssets->where('category', 'sim')->count()],
            ['label' => 'ID Card', 'category' => 'id_card', 'total' => $allFilteredAssets->where('category', 'id_card')->count()],
            ['label' => 'Other', 'category' => 'other', 'total' => $allFilteredAssets->where('category', 'other')->count()],
        ];

        // Paginated Assets List
        $assets = (clone $assetsQuery)
            ->with(['branch', 'department', 'creator'])
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Assets fetched successfully.',
            'summary' => [
                'total_assets' => $totalAssets,
                'available_assets' => $availableAssets,
                'issued_assets' => $issuedAssets,
                'damaged_assets' => $damagedAssets,
                'total_asset_value' => $totalAssetValue,
            ],
            'category_breakdown' => $categoryBreakdown,
            'data' => $assets,
        ]);
    }

    /**
     * Generate Next Asset Code
     */
    public function generateCode(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();
        $category = $request->input('category', 'laptop');

        $prefixMap = [
            'laptop' => 'LAP',
            'mobile' => 'MOB',
            'sim' => 'SIM',
            'id_card' => 'ID',
            'other' => 'AST',
        ];

        $prefix = $prefixMap[$category] ?? 'AST';
        $count = Asset::where('partner_id', $partnerId)->where('category', $category)->count() + 1;
        $assetCode = sprintf('%s-%04d', $prefix, $count);

        return response()->json([
            'status' => 'success',
            'message' => 'Asset code generated successfully.',
            'asset_code' => $assetCode,
        ]);
    }

    /**
     * Get Single Asset Details
     */
    public function show($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $asset = Asset::where('partner_id', $partnerId)
            ->with(['branch', 'department', 'creator'])
            ->find($id);

        if (!$asset) {
            return response()->json([
                'status' => 'error',
                'message' => 'Asset record not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Asset details fetched successfully.',
            'data' => $asset,
        ]);
    }

    /**
     * Register New Asset
     */
    public function store(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();
        $user = auth('hrms_api')->user();

        $validated = $request->validate([
            'asset_code' => 'required|string|max:100',
            'category' => 'required|in:laptop,mobile,sim,id_card,other',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'imei_number' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|max:50',
            'sim_number' => 'nullable|string|max:100',
            'card_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'condition' => 'required|in:new,good,fair,damaged,lost',
            'status' => 'required|in:available,issued,damaged',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string|max:1000',
        ]);

        // Check Unique Asset Code for Partner Organization
        $existing = Asset::where('partner_id', $partnerId)
            ->where('asset_code', $validated['asset_code'])
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'This asset code is already in use for your organization.'
            ], 422);
        }

        $asset = Asset::create([
            'partner_id' => $partnerId,
            'asset_code' => $validated['asset_code'],
            'category' => $validated['category'],
            'name' => $validated['name'],
            'brand' => $validated['brand'] ?? null,
            'model' => $validated['model'] ?? null,
            'serial_number' => $validated['serial_number'] ?? null,
            'imei_number' => $validated['imei_number'] ?? null,
            'mobile_number' => $validated['mobile_number'] ?? null,
            'sim_number' => $validated['sim_number'] ?? null,
            'card_number' => $validated['card_number'] ?? null,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'purchase_cost' => $validated['purchase_cost'] ?? null,
            'condition' => $validated['condition'],
            'status' => $validated['status'],
            'branch_id' => $validated['branch_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Asset registered successfully.',
            'data' => $asset->load(['branch', 'department']),
        ], 201);
    }

    /**
     * Update Asset Details
     */
    public function update(Request $request, $id): JsonResponse
    {
        $partnerId = $this->getPartnerId();
        $user = auth('hrms_api')->user();

        $asset = Asset::where('partner_id', $partnerId)->find($id);

        if (!$asset) {
            return response()->json([
                'status' => 'error',
                'message' => 'Asset record not found.'
            ], 404);
        }

        $validated = $request->validate([
            'asset_code' => 'sometimes|required|string|max:100',
            'category' => 'sometimes|required|in:laptop,mobile,sim,id_card,other',
            'name' => 'sometimes|required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'imei_number' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|max:50',
            'sim_number' => 'nullable|string|max:100',
            'card_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'condition' => 'sometimes|required|in:new,good,fair,damaged,lost',
            'status' => 'sometimes|required|in:available,issued,damaged',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string|max:1000',
        ]);

        if (isset($validated['asset_code']) && $validated['asset_code'] !== $asset->asset_code) {
            $existing = Asset::where('partner_id', $partnerId)
                ->where('asset_code', $validated['asset_code'])
                ->where('id', '!=', $asset->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This asset code is already in use for your organization.'
                ], 422);
            }
        }

        $validated['updated_by'] = $user->id;
        $asset->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Asset updated successfully.',
            'data' => $asset->fresh(['branch', 'department']),
        ]);
    }

    /**
     * Delete Asset
     */
    public function destroy($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $asset = Asset::where('partner_id', $partnerId)->find($id);

        if (!$asset) {
            return response()->json([
                'status' => 'error',
                'message' => 'Asset record not found.'
            ], 404);
        }

        $asset->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Asset deleted successfully.'
        ]);
    }

    /**
     * Export Assets Report as CSV stream
     */
    public function exportCsv(Request $request)
    {
        $partnerId = $this->getPartnerId();

        $category = $request->input('category', 'all');
        $status = $request->input('status', 'all');
        $condition = $request->input('condition', 'all');
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');

        $query = Asset::where('partner_id', $partnerId);

        if ($category !== 'all' && !empty($category)) $query->where('category', $category);
        if ($status !== 'all' && !empty($status)) $query->where('status', $status);
        if ($condition !== 'all' && !empty($condition)) $query->where('condition', $condition);
        if (!empty($branchId)) $query->where('branch_id', $branchId);
        if (!empty($departmentId)) $query->where('department_id', $departmentId);

        $assets = $query->with(['branch', 'department'])->get();

        $filename = "assets_report_" . date('Y_m_d_H_i') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($assets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Asset Code', 'Category', 'Name', 'Brand', 'Model', 'Serial No', 'IMEI No', 'Mobile No', 'SIM No', 'Card No', 'Branch', 'Department', 'Purchase Date', 'Purchase Cost', 'Condition', 'Status']);

            foreach ($assets as $a) {
                fputcsv($file, [
                    $a->asset_code,
                    ucfirst($a->category),
                    $a->name,
                    $a->brand ?? 'N/A',
                    $a->model ?? 'N/A',
                    $a->serial_number ?? 'N/A',
                    $a->imei_number ?? 'N/A',
                    $a->mobile_number ?? 'N/A',
                    $a->sim_number ?? 'N/A',
                    $a->card_number ?? 'N/A',
                    $a->branch?->name ?? 'N/A',
                    $a->department?->name ?? 'N/A',
                    $a->purchase_date ? $a->purchase_date->format('Y-m-d') : 'N/A',
                    $a->purchase_cost ? number_format($a->purchase_cost, 2) : '0.00',
                    ucfirst($a->condition),
                    ucfirst($a->status),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
