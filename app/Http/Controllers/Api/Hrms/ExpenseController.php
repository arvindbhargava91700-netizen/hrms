<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Get own expenses
     */
public function index(Request $request): JsonResponse
{
    $user = auth('hrms_api')->user();

    $expenses = Expense::where('employee_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    $data = $expenses->map(function ($expense) {
        return [
            'id' => $expense->id,
            'employee_id' => $expense->employee_id,
            'expense_category_id' => $expense->expense_category_id,
            'amount' => $expense->amount,
            'quantity' => $expense->quantity,
            'unit_rate' => $expense->unit_rate,
            'category' => $expense->category,
            'date' => $expense->date,
            'description' => $expense->description,
            'status' => $expense->status,
            'remarks' => $expense->remarks,
            'upload_file' => $expense->upload_file
                ? url(\Storage::url($expense->upload_file))
                : null,
            'created_at' => $expense->created_at,
            'updated_at' => $expense->updated_at,
        ];
    });

    return response()->json([
        'status' => 'success',
        'message' => $data->isEmpty()
            ? 'No expenses found.'
            : 'Expenses fetched successfully.',
        'data' => $data,
    ]);
}

    /**
     * Submit an expense claim
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'upload_file' => 'required|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('upload_file')) {
            $filePath = $request->file('upload_file')->store('expenses', 'public');
        }

        $expense = Expense::create([
            'employee_id' => $user->id,
            'amount' => $request->amount,
            'category' => $request->category,
            'date' => $request->date,
            'description' => $request->description,
            'status' => 'pending',
            'upload_file' => $filePath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense submitted successfully.',
            'data' => $expense,
        ], 201);
    }

    /**
     * Get team expenses
     */
    public function teamExpenses(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $user->getTeamIds();

        $query = Expense::whereIn('employee_id', $teamIds)->with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $expenses = $query->orderBy('created_at', 'desc')->paginate(15);

        $message = $expenses->isEmpty() ? 'No team expenses found.' : 'Team expenses fetched successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $expenses,
        ]);
    }

    /**
     * Update expense status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $user->getTeamIds();

        $expense = Expense::findOrFail($id);

        if (! in_array($expense->employee_id, $teamIds)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized or employee not in team.'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $expense->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Expense status updated to {$request->status}.",
            'data' => $expense,
        ]);
    }

    /**
     * Update an expense
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $expense = Expense::findOrFail($id);

        if ($expense->employee_id !== $user->id && ! in_array($expense->employee_id, $user->getTeamIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'upload_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:10240',
        ]);

        $updateData = [
            'amount' => $request->amount,
            'category' => $request->category,
            'date' => $request->date,
            'description' => $request->description,
        ];

        if ($request->hasFile('upload_file')) {
            $updateData['upload_file'] = $request->file('upload_file')->store('expenses', 'public');
        }

        $expense->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense updated successfully.',
            'data' => $expense,
        ]);
    }

    /**
     * Get a single expense detail
     */
    public function show($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $expense = Expense::with('employee')->findOrFail($id);

        if ($expense->employee_id !== $user->id && ! in_array($expense->employee_id, $user->getTeamIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $data = [
            'id' => $expense->id,
            'employee_id' => $expense->employee_id,
            'expense_category_id' => $expense->expense_category_id,
            'amount' => $expense->amount,
            'quantity' => $expense->quantity,
            'unit_rate' => $expense->unit_rate,
            'category' => $expense->category,
            'date' => $expense->date,
            'description' => $expense->description,
            'status' => $expense->status,
            'remarks' => $expense->remarks,
            'upload_file' => $expense->upload_file
                ? url(\Storage::url($expense->upload_file))
                : null,
            'created_at' => $expense->created_at,
            'updated_at' => $expense->updated_at,
            'employee' => $expense->employee ? [
                'id' => $expense->employee->id,
                'name' => $expense->employee->name,
                'email' => $expense->employee->email,
            ] : null,
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Expense fetched successfully.',
            'data' => $data,
        ]);
    }

    /**
     * Delete an expense
     */
    public function destroy($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $expense = Expense::findOrFail($id);

        if ($expense->employee_id !== $user->id && ! in_array($expense->employee_id, $user->getTeamIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $expense->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense deleted successfully.',
        ]);
    }
}
