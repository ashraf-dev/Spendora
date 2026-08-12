<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ExpenseStatisticsService
{
    /**
     * @return array{
     *     latest_expenses: Collection<int, Expense>,
     *     totals: array{today: string, current_month: string, previous_month: string, current_year: string, same_day_last_year: string},
     *     comparisons: array{month_over_month_percentage: float, today_vs_same_day_last_year_percentage: float},
     *     current_month_by_category: Collection<int, array{category: Category, total_amount: string, expense_count: int}>
     * }
     */
    public function dashboard(User $user): array
    {
        $today = now()->startOfDay();
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $previousMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = now()->subMonthNoOverflow()->endOfMonth();
        $yearStart = now()->startOfYear();
        $yearEnd = now()->endOfYear();
        $sameDayLastYear = now()->subYear()->startOfDay();

        $latestExpenses = Expense::query()
            ->forUser($user)
            ->with('category')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $todayTotal = $this->sumForPeriod($user, $today, $today->copy()->endOfDay());
        $currentMonthTotal = $this->sumForPeriod($user, $currentMonthStart, $currentMonthEnd);
        $previousMonthTotal = $this->sumForPeriod($user, $previousMonthStart, $previousMonthEnd);
        $yearTotal = $this->sumForPeriod($user, $yearStart, $yearEnd);
        $sameDayLastYearTotal = $this->sumForPeriod($user, $sameDayLastYear, $sameDayLastYear->copy()->endOfDay());

        $monthChangePercentage = $this->percentageChange($currentMonthTotal, $previousMonthTotal);
        $todayVsLastYearPercentage = $this->percentageChange($todayTotal, $sameDayLastYearTotal);

        $categoryTotals = $this->categoryTotalsForPeriod($user, $currentMonthStart, $currentMonthEnd);

        return [
            'latest_expenses' => $latestExpenses,
            'totals' => [
                'today' => $todayTotal,
                'current_month' => $currentMonthTotal,
                'previous_month' => $previousMonthTotal,
                'current_year' => $yearTotal,
                'same_day_last_year' => $sameDayLastYearTotal,
            ],
            'comparisons' => [
                'month_over_month_percentage' => $monthChangePercentage,
                'today_vs_same_day_last_year_percentage' => $todayVsLastYearPercentage,
            ],
            'current_month_by_category' => $categoryTotals,
        ];
    }

    /**
     * @return array{
     *     month: int,
     *     year: int,
     *     selected_month_total: string,
     *     previous_month_total: string,
     *     current_year_total: string,
     *     expense_count: int,
     *     highest_expense: string,
     *     average_expense: string,
     *     daily_totals: list<array{date: string, total: string}>,
     *     category_totals: Collection<int, array{category: Category, total_amount: string, expense_count: int}>
     * }
     */
    public function monthly(User $user, int $month, int $year): array
    {
        $start = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();
        $previousStart = $start->copy()->subMonthNoOverflow()->startOfMonth();
        $previousEnd = $start->copy()->subMonthNoOverflow()->endOfMonth();
        $yearStart = CarbonImmutable::create($year, 1, 1)->startOfDay();
        $yearEnd = CarbonImmutable::create($year, 12, 31)->endOfDay();

        $selectedTotal = $this->sumForPeriod($user, $start, $end);
        $previousTotal = $this->sumForPeriod($user, $previousStart, $previousEnd);
        $yearTotal = $this->sumForPeriod($user, $yearStart, $yearEnd);

        $stats = Expense::query()
            ->forUser($user)
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('COUNT(*) as expense_count')
            ->selectRaw('COALESCE(MAX(amount), 0) as highest_expense')
            ->selectRaw('COALESCE(AVG(amount), 0) as average_expense')
            ->first();

        return [
            'month' => $month,
            'year' => $year,
            'selected_month_total' => $selectedTotal,
            'previous_month_total' => $previousTotal,
            'current_year_total' => $yearTotal,
            'expense_count' => (int) ($stats->expense_count ?? 0),
            'highest_expense' => $this->formatAmount($stats->highest_expense ?? 0),
            'average_expense' => $this->formatAmount($stats->average_expense ?? 0),
            'daily_totals' => $this->dailyTotals($user, $start, $end),
            'category_totals' => $this->categoryTotalsForPeriod($user, $start, $end),
        ];
    }

    /**
     * @return array{
     *     month: int,
     *     year: int,
     *     month_total: string,
     *     categories: Collection<int, array{
     *         category: Category,
     *         total_amount: string,
     *         expense_count: int,
     *         percentage: float,
     *         recent_expenses: EloquentCollection<int, Expense>
     *     }>
     * }
     */
    public function categories(User $user, int $month, int $year): array
    {
        $start = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();
        $monthTotal = $this->sumForPeriod($user, $start, $end);

        $activeCategories = Category::query()->active()->orderBy('id')->get();

        $totalsByCategory = Expense::query()
            ->forUser($user)
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->select('category_id')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->selectRaw('COUNT(*) as expense_count')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        $recentByCategory = Expense::query()
            ->forUser($user)
            ->with('category')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('category_id');

        $categories = $activeCategories->map(function (Category $category) use ($totalsByCategory, $recentByCategory, $monthTotal) {
            $row = $totalsByCategory->get($category->id);
            $total = $this->formatAmount($row->total ?? 0);
            $count = (int) ($row->expense_count ?? 0);

            return [
                'category' => $category,
                'total_amount' => $total,
                'expense_count' => $count,
                'percentage' => $this->percentageOfTotal($total, $monthTotal),
                'recent_expenses' => ($recentByCategory->get($category->id) ?? (new Expense)->newCollection())
                    ->take(5)
                    ->values(),
            ];
        })->values();

        return [
            'month' => $month,
            'year' => $year,
            'month_total' => $monthTotal,
            'categories' => $categories,
        ];
    }

    /**
     * @return array{
     *     category: Category,
     *     month: int,
     *     year: int,
     *     selected_month_total: string,
     *     expense_count: int,
     *     expenses: LengthAwarePaginator<int, Expense>,
     *     navigation: array{
     *         previous_month: array{month: int, year: int},
     *         next_month: array{month: int, year: int}
     *     }
     * }
     */
    public function categoryDetails(
        User $user,
        Category $category,
        int $month,
        int $year,
        int $perPage = 15
    ): array {
        $start = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        $query = Expense::query()
            ->forUser($user)
            ->where('category_id', $category->id)
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);

        $total = $this->formatAmount((clone $query)->sum('amount'));
        $count = (clone $query)->count();

        $expenses = $query
            ->with('category')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate($perPage);

        $previous = $start->copy()->subMonthNoOverflow();
        $next = $start->copy()->addMonthNoOverflow();

        return [
            'category' => $category,
            'month' => $month,
            'year' => $year,
            'selected_month_total' => $total,
            'expense_count' => $count,
            'expenses' => $expenses,
            'navigation' => [
                'previous_month' => [
                    'month' => $previous->month,
                    'year' => $previous->year,
                ],
                'next_month' => [
                    'month' => $next->month,
                    'year' => $next->year,
                ],
            ],
        ];
    }

    private function sumForPeriod(User $user, CarbonInterface $start, CarbonInterface $end): string
    {
        $query = Expense::query()->forUser($user);

        if ($start->toDateString() === $end->toDateString()) {
            $query->whereDate('expense_date', $start->toDateString());
        } else {
            $query->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);
        }

        return $this->formatAmount($query->sum('amount'));
    }

    /**
     * @return list<array{date: string, total: string}>
     */
    private function dailyTotals(User $user, CarbonInterface $start, CarbonInterface $end): array
    {
        $rows = Expense::query()
            ->forUser($user)
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->select('expense_date')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupBy('expense_date')
            ->get()
            ->mapWithKeys(function (Expense $expense) {
                return [
                    $expense->expense_date->toDateString() => $expense->getAttribute('total'),
                ];
            });

        $daily = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $key = $date->toDateString();
            $daily[] = [
                'date' => $key,
                'total' => $this->formatAmount($rows[$key] ?? 0),
            ];
        }

        return $daily;
    }

    /**
     * @return Collection<int, array{category: Category, total_amount: string, expense_count: int}>
     */
    private function categoryTotalsForPeriod(User $user, CarbonInterface $start, CarbonInterface $end): Collection
    {
        $totals = Expense::query()
            ->forUser($user)
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->select('category_id')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->selectRaw('COUNT(*) as expense_count')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        return Category::query()
            ->active()
            ->orderBy('id')
            ->get()
            ->map(function (Category $category) use ($totals) {
                $row = $totals->get($category->id);

                return [
                    'category' => $category,
                    'total_amount' => $this->formatAmount($row->total ?? 0),
                    'expense_count' => (int) ($row->expense_count ?? 0),
                ];
            })
            ->values();
    }

    private function percentageChange(string $current, string $previous): float
    {
        $currentAmount = (float) $current;
        $previousAmount = (float) $previous;

        if ($previousAmount == 0.0) {
            return $currentAmount == 0.0 ? 0.0 : 100.0;
        }

        return round((($currentAmount - $previousAmount) / $previousAmount) * 100, 2);
    }

    private function percentageOfTotal(string $amount, string $total): float
    {
        $totalAmount = (float) $total;

        if ($totalAmount == 0.0) {
            return 0.0;
        }

        return round(((float) $amount / $totalAmount) * 100, 2);
    }

    private function formatAmount(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
