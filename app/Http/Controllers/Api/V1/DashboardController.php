<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ExpenseResource;
use App\Http\Responses\ApiResponse;
use App\Services\ExpenseStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private ExpenseStatisticsService $statistics) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $this->statistics->dashboard($request->user());

        return ApiResponse::success([
            'latest_expenses' => ExpenseResource::collection($data['latest_expenses']),
            'totals' => $data['totals'],
            'comparisons' => $data['comparisons'],
            'current_month_by_category' => collect($data['current_month_by_category'])->map(fn (array $row) => [
                'category' => new CategoryResource($row['category']),
                'total_amount' => $row['total_amount'],
                'expense_count' => $row['expense_count'],
            ])->values(),
        ], __('api.dashboard.retrieved'));
    }
}
