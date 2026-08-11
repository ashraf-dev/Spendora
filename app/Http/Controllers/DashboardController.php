<?php

namespace App\Http\Controllers;

use App\Services\ExpenseStatisticsService;
use App\Support\SpendoraPayload;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private ExpenseStatisticsService $statistics) {}

    public function __invoke(Request $request): Response
    {
        $data = $this->statistics->dashboard($request->user());

        return Inertia::render('Dashboard', [
            'latest_expenses' => SpendoraPayload::expenses($data['latest_expenses']),
            'totals' => $data['totals'],
            'comparisons' => $data['comparisons'],
            'current_month_by_category' => collect($data['current_month_by_category'])
                ->map(fn (array $row) => SpendoraPayload::categoryTotalRow($row))
                ->values()
                ->all(),
        ]);
    }
}
