<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Get Product Categories
     */
    public function getCategories(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = ProductCategory::where('partner_id', $partnerId);

        if ($request->filled('search') || $request->filled('name')) {
            $searchTerm = $request->input('search', $request->input('name'));
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'active');
        }

        $categories = $query->withCount('products')->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Create Product Category
     */
    public function createCategory(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('product_categories_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }
        
     
        $request->validate(['name' => 'required|string|max:255']);
        $category = ProductCategory::create([
            'name' => $request->name, 
            'partner_id' => $user->partner_id ?? $user->id,
            'status' => 'active'
        ]);

        return response()->json(['status' => 'success', 'message' => 'Category added successfully.','data' => $category], 201);
    }


     /**
     *Update Category
     */

    public function updateCategory(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('product_categories_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }
        $partnerId=$user->partner_id ??  $user->id;

        $request->validate(['name' => 'required|string|max:255']);
        $category = ProductCategory::where('id', $id)->where('partner_id', $partnerId)->firstOrFail();
        $category->update(['name' => $request->name]);

        return response()->json(['status' => 'success','message' => 'Category updated successfully.', 'data' => $category]);
    }
    /**
     *delete Category
     */
    public function deleteCategory($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('product_categories_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }
         $partnerId=$user->partner_id ??  $user->id;


        $category = ProductCategory::where('id', $id)->where('partner_id', $partnerId)->firstOrFail();
        // Maybe check for associated products first, but skipping for simplicity or soft deleting
        $category->update(['status' => 'inactive']); // Soft delete by status

        return response()->json(['status' => 'success', 'message' => 'Category deleted successfully.']);
    }



/**
     * Get Products (mirrors web Products page)
     */
    public function getProducts(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->partner_id ?? $user->id;

        $query = Product::where('partner_id', $partnerId)
            ->with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('name')->get();

        $data = $products->map(fn ($product) => $this->presentProduct($product))->values();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Create Product
     */
    public function createProduct(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('products_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->partner_id ?? $user->id;
        $gstType = $request->input('gst_type', 'notinclude');

        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:product_categories,id',
            'amount' => 'required|numeric|min:0',
            'gst_type' => 'sometimes|in:include,notinclude',
            'status' => 'sometimes|in:active,inactive',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ];
        $rules['gst_percent'] = $gstType === 'include' ? 'required|numeric|min:0.01|max:100' : 'nullable|numeric|min:0';

        $request->validate($rules, [
            'gst_percent.required' => 'Please specify the GST percentage when GST is included.',
            'gst_percent.min' => 'GST percentage must be greater than 0%.',
        ]);

        $data = [
            'name' => $request->name,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'gst_type' => $gstType,
            'gst_percent' => $gstType === 'include' ? (float) $request->input('gst_percent', 0) : 0,
            'partner_id' => $partnerId,
            'status' => $request->input('status', 'active')
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data)->load('category');

        return response()->json(['status' => 'success', 'message' => 'Product added successfully.', 'data' => $this->presentProduct($product)], 201);
    }

    public function updateProduct(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('products_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->partner_id ?? $user->id;

        $product = Product::where('id', $id)->where('partner_id', $partnerId)->firstOrFail();

        $gstType = $request->input('gst_type', $product->gst_type ?: 'notinclude');

        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:product_categories,id',
            'amount' => 'required|numeric|min:0',
            'gst_type' => 'sometimes|in:include,notinclude',
            'status' => 'sometimes|in:active,inactive',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ];
        $rules['gst_percent'] = $gstType === 'include' ? 'required|numeric|min:0.01|max:100' : 'nullable|numeric|min:0';

        $request->validate($rules, [
            'gst_percent.required' => 'Please specify the GST percentage when GST is included.',
            'gst_percent.min' => 'GST percentage must be greater than 0%.',
        ]);

        $data = [
            'name' => $request->name,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'gst_type' => $gstType,
            'gst_percent' => $gstType === 'include' ? (float) $request->input('gst_percent', 0) : 0,
            'status' => $request->input('status', $product->status ?? 'active'),
        ];

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return response()->json(['status' => 'success', 'message' => 'Product update successfully.', 'data' => $this->presentProduct($product->fresh('category'))]);
    }

    public function deleteProduct($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('products_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }
         $partnerId=$user->partner_id ??  $user->id;

        $product = Product::where('id', $id)->where('partner_id', $partnerId)->firstOrFail();
        $product->update(['status' => 'inactive']); // Soft delete

        return response()->json(['status' => 'success', 'message' => 'Product deleted successfully.']);
    }

    /**
     * Present a product in the shape of the web Products page
     */
    private function presentProduct(Product $product): array
    {
        $isGstIncluded = $product->gst_type === 'include' && (float) $product->gst_percent > 0;
        $gstRate = $isGstIncluded ? (float) $product->gst_percent : 0;
        $gstAmount = $isGstIncluded ? round((float) $product->amount * $gstRate / 100, 2) : 0;

        return [
            'id'            => $product->id,
            'name'          => $product->name,
            'category_id'   => $product->category_id,
            'category_name' => $product->category->name ?? 'Uncategorized',
            'amount'        => (float) $product->amount,
            'gst_type'      => $product->gst_type,
            'gst_percent'   => (float) $product->gst_percent,
            'gst_amount'    => $gstAmount,
            'total_price'   => round((float) $product->amount + $gstAmount, 2),
            'status'        => $product->status,
        ];
    }
}
