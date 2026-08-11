<?php

namespace App\Http\Controllers;

use App\Services\ExpenseStatisticsService;
use App\Support\SpendoraPayload;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(private ExpenseStatisticsService $statistics) {}

    public function __invoke(Request $request): Response
    {
        $month = (int) $request->integer('month', now()->month);
        $year = (int) $request->integer('year', now()->year);

        $data = $this->statistics->monthly($request->user(), $month, $year);

        $current = now()->setDate($year, $month, 1)->startOfMonth();

        return Inertia::render('Analytics', [
            'month' => $data['month'],
            'year' => $data['year'],
            'selected_month_total' => $data['selected_month_total'],
            'previous_month_total' => $data['previous_month_total'],
            'current_year_total' => $data['current_year_total'],
            'expense_count' => $data['expense_count'],
            'highest_expense' => $data['highest_expense'],
            'average_expense' => $data['average_expense'],
            'daily_totals' => $data['daily_totals'],
            'category_totals' => collect($data['category_totals'])
                ->map(fn (array $row) => SpendoraPayload::categoryTotalRow($row))
                ->values()
                ->all(),
            'navigation' => [
                'previous_month' => [
                    'month' => $current->copy()->subMonthNoOverflow()->month,
                    'year' => $current->copy()->subMonthNoOverflow()->year,
                ],
                'next_month' => [
                    'month' => $current->copy()->addMonthNoOverflow()->month,
                    'year' => $current->copy()->addMonthNoOverflow()->year,
                ],
            ],
        ]);
    }
}
