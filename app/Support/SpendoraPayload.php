<?php

namespace App\Support;

use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SpendoraPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function category(Category $category): array
    {
        return (new CategoryResource($category))->resolve();
    }

    /**
     * @param  Collection<int, Category>|iterable<int, Category>  $categories
     * @return list<array<string, mixed>>
     */
    public static function categories(iterable $categories): array
    {
        return collect($categories)
            ->map(fn (Category $category) => self::category($category))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function expense(Expense $expense): array
    {
        return [
            'id' => $expense->id,
            'category_id' => $expense->category_id,
            'expense_date' => $expense->expense_date?->toDateString(),
            'description' => $expense->description,
            'amount' => $expense->amount,
            'category' => $expense->relationLoaded('category') && $expense->category !== null
                ? self::category($expense->category)
                : null,
            'created_at' => $expense->created_at,
            'updated_at' => $expense->updated_at,
        ];
    }

    /**
     * @param  Collection<int, Expense>|iterable<int, Expense>  $expenses
     * @return list<array<string, mixed>>
     */
    public static function expenses(iterable $expenses): array
    {
        return collect($expenses)
            ->map(fn (Expense $expense) => self::expense($expense))
            ->values()
            ->all();
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array<string, int>}
     */
    public static function paginatedExpenses(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => self::expenses($paginator->getCollection()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function categoryTotalRow(array $row): array
    {
        return [
            'category' => self::category($row['category']),
            'total_amount' => $row['total_amount'],
            'expense_count' => $row['expense_count'],
            ...array_key_exists('percentage', $row) ? ['percentage' => $row['percentage']] : [],
            ...array_key_exists('recent_expenses', $row)
                ? ['recent_expenses' => self::expenses($row['recent_expenses'])]
                : [],
        ];
    }
}
