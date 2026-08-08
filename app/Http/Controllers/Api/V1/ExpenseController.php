<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ExpenseIndexRequest;
use App\Http\Requests\Api\V1\StoreExpenseRequest;
use App\Http\Requests\Api\V1\UpdateExpenseRequest;
use App\Http\Resources\Api\V1\ExpenseCollection;
use App\Http\Resources\Api\V1\ExpenseResource;
use App\Http\Responses\ApiResponse;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpenseController extends Controller
{
    public function index(ExpenseIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);
        $sort = $validated['sort'] ?? 'expense_date';
        $direction = $validated['direction'] ?? 'desc';

        $query = Expense::query()
            ->forUser($request->user())
            ->with('category');

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (isset($validated['month'], $validated['year'])) {
            $query->whereMonth('expense_date', $validated['month'])
                ->whereYear('expense_date', $validated['year']);
        } elseif (isset($validated['year'])) {
            $query->whereYear('expense_date', $validated['year']);
        } elseif (isset($validated['month'])) {
            $query->whereMonth('expense_date', $validated['month']);
        }

        if (isset($validated['date_from'])) {
            $query->whereDate('expense_date', '>=', $validated['date_from']);
        }

        if (isset($validated['date_to'])) {
            $query->whereDate('expense_date', '<=', $validated['date_to']);
        }

        if (! empty($validated['search'])) {
            $query->where('description', 'like', '%'.$validated['search'].'%');
        }

        $expenses = $query
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate($perPage);

        return ApiResponse::success(
            (new ExpenseCollection($expenses))->resolve(),
            __('api.expense.retrieved')
        );
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $request->user()->expenses()->create($request->validated());
        $expense->load('category');

        return ApiResponse::success(
            new ExpenseResource($expense),
            __('api.expense.created'),
            201
        );
    }

    public function show(Request $request, Expense $expense): JsonResponse
    {
        $this->ensureOwned($request, $expense);
        $expense->load('category');

        return ApiResponse::success(
            new ExpenseResource($expense),
            __('api.expense.show')
        );
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->ensureOwned($request, $expense);
        $expense->update($request->validated());
        $expense->load('category');

        return ApiResponse::success(
            new ExpenseResource($expense),
            __('api.expense.updated')
        );
    }

    public function destroy(Request $request, Expense $expense): Response
    {
        $this->ensureOwned($request, $expense);
        $expense->delete();

        return response()->noContent();
    }

    private function ensureOwned(Request $request, Expense $expense): void
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(404);
        }
    }
}
