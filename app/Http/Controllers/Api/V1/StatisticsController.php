<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MonthlyStatisticsRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ExpenseCollection;
use App\Http\Resources\Api\V1\ExpenseResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use App\Services\ExpenseStatisticsService;
use Illuminate\Http\JsonResponse;

class StatisticsController extends Controller
{
    public function __construct(private ExpenseStatisticsService $statistics) {}

    public function monthly(MonthlyStatisticsRequest $request): JsonResponse
    {
        $data = $this->statistics->monthly(
            $request->user(),
            $request->month(),
            $request->year()
        );

        return ApiResponse::success([
            'month' => $data['month'],
            'year' => $data['year'],
            'selected_month_total' => $data['selected_month_total'],
            'previous_month_total' => $data['previous_month_total'],
            'current_year_total' => $data['current_year_total'],
            'expense_count' => $data['expense_count'],
            'highest_expense' => $data['highest_expense'],
            'average_expense' => $data['average_expense'],
            'daily_totals' => $data['daily_totals'],
            'category_totals' => collect($data['category_totals'])->map(fn (array $row) => [
                'category' => new CategoryResource($row['category']),
                'total_amount' => $row['total_amount'],
                'expense_count' => $row['expense_count'],
            ])->values(),
        ], __('api.statistics.monthly_retrieved'));
    }

    public function categories(MonthlyStatisticsRequest $request): JsonResponse
    {
        $data = $this->statistics->categories(
            $request->user(),
            $request->month(),
            $request->year()
        );

        return ApiResponse::success([
            'month' => $data['month'],
            'year' => $data['year'],
            'month_total' => $data['month_total'],
            'categories' => collect($data['categories'])->map(fn (array $row) => [
                'category' => new CategoryResource($row['category']),
                'total_amount' => $row['total_amount'],
                'expense_count' => $row['expense_count'],
                'percentage' => $row['percentage'],
                'recent_expenses' => ExpenseResource::collection($row['recent_expenses']),
            ])->values(),
        ], __('api.statistics.categories_retrieved'));
    }

    public function category(MonthlyStatisticsRequest $request, Category $category): JsonResponse
    {
        if (! $category->is_active) {
            abort(404);
        }

        $perPage = (int) ($request->validated('per_page') ?? 15);

        $data = $this->statistics->categoryDetails(
            $request->user(),
            $category,
            $request->month(),
            $request->year(),
            $perPage
        );

        return ApiResponse::success([
            'category' => new CategoryResource($data['category']),
            'month' => $data['month'],
            'year' => $data['year'],
            'selected_month_total' => $data['selected_month_total'],
            'expense_count' => $data['expense_count'],
            'expenses' => (new ExpenseCollection($data['expenses']))->resolve(),
            'navigation' => $data['navigation'],
        ], __('api.statistics.category_retrieved'));
    }
}
