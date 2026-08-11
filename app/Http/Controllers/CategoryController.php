<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ExpenseStatisticsService;
use App\Support\SpendoraPayload;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(private ExpenseStatisticsService $statistics) {}

    public function index(Request $request): Response
    {
        $month = (int) $request->integer('month', now()->month);
        $year = (int) $request->integer('year', now()->year);

        $data = $this->statistics->categories($request->user(), $month, $year);

        return Inertia::render('categories/Index', [
            'month' => $data['month'],
            'year' => $data['year'],
            'month_total' => $data['month_total'],
            'categories' => collect($data['categories'])
                ->map(fn (array $row) => SpendoraPayload::categoryTotalRow($row))
                ->values()
                ->all(),
            'navigation' => $this->monthNavigation($month, $year),
        ]);
    }

    public function show(Request $request, Category $category): Response
    {
        abort_unless($category->is_active, 404);

        $month = (int) $request->integer('month', now()->month);
        $year = (int) $request->integer('year', now()->year);
        $perPage = (int) $request->integer('per_page', 15);

        $data = $this->statistics->categoryDetails(
            $request->user(),
            $category,
            $month,
            $year,
            $perPage
        );

        return Inertia::render('categories/Show', [
            'category' => SpendoraPayload::category($data['category']),
            'month' => $data['month'],
            'year' => $data['year'],
            'selected_month_total' => $data['selected_month_total'],
            'expense_count' => $data['expense_count'],
            'expenses' => SpendoraPayload::paginatedExpenses($data['expenses']),
            'navigation' => $data['navigation'],
        ]);
    }

    /**
     * @return array{previous_month: array{month: int, year: int}, next_month: array{month: int, year: int}}
     */
    private function monthNavigation(int $month, int $year): array
    {
        $current = now()->setDate($year, $month, 1)->startOfMonth();

        return [
            'previous_month' => [
                'month' => $current->copy()->subMonthNoOverflow()->month,
                'year' => $current->copy()->subMonthNoOverflow()->year,
            ],
            'next_month' => [
                'month' => $current->copy()->addMonthNoOverflow()->month,
                'year' => $current->copy()->addMonthNoOverflow()->year,
            ],
        ];
    }
}
