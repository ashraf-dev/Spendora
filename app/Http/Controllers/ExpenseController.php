<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expenses\ExpenseIndexRequest;
use App\Http\Requests\Expenses\StoreExpenseRequest;
use App\Http\Requests\Expenses\UpdateExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use App\Support\SpendoraPayload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(ExpenseIndexRequest $request): Response
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

        $totalAmount = number_format((float) (clone $query)->sum('amount'), 2, '.', '');

        $expenses = $query
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('expenses/Index', [
            'expenses' => SpendoraPayload::paginatedExpenses($expenses),
            'total_amount' => $totalAmount,
            'categories' => SpendoraPayload::categories(
                Category::query()->active()->orderBy('id')->get()
            ),
            'filters' => [
                'month' => $validated['month'] ?? null,
                'year' => $validated['year'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'date_from' => $validated['date_from'] ?? null,
                'date_to' => $validated['date_to'] ?? null,
                'search' => $validated['search'] ?? null,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('expenses/Form', [
            'expense' => null,
            'categories' => SpendoraPayload::categories(
                Category::query()->active()->orderBy('id')->get()
            ),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $request->user()->expenses()->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Expense created.'),
        ]);

        return to_route('expenses.index');
    }

    public function edit(Request $request, Expense $expense): Response
    {
        $this->ensureOwned($request, $expense);

        $expense->load('category');

        return Inertia::render('expenses/Form', [
            'expense' => SpendoraPayload::expense($expense),
            'categories' => SpendoraPayload::categories(
                Category::query()->active()->orderBy('id')->get()
            ),
        ]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->ensureOwned($request, $expense);

        $expense->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Expense updated.'),
        ]);

        return to_route('expenses.index');
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        $this->ensureOwned($request, $expense);

        $expense->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Expense deleted.'),
        ]);

        return to_route('expenses.index');
    }

    private function ensureOwned(Request $request, Expense $expense): void
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(404);
        }
    }
}
