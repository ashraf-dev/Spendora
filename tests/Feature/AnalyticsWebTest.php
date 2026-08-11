<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;

test('authenticated users can view monthly analytics', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $month = 8;
    $year = 2026;

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => '2026-08-10',
        'amount' => '30.00',
    ]);

    $this->actingAs($user)
        ->get(route('analytics', ['month' => $month, 'year' => $year]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Analytics')
            ->where('month', $month)
            ->where('year', $year)
            ->where('selected_month_total', '30.00')
            ->where('expense_count', 1)
            ->has('daily_totals', 31)
            ->where('daily_totals.9.date', '2026-08-10')
            ->where('daily_totals.9.total', '30.00')
            ->where('daily_totals.0.total', '0.00')
            ->has('category_totals')
            ->has('navigation'));
});

test('analytics month navigation wraps years correctly', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('analytics', ['month' => 1, 'year' => 2026]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('navigation.previous_month.month', 12)
            ->where('navigation.previous_month.year', 2025)
            ->where('navigation.next_month.month', 2)
            ->where('navigation.next_month.year', 2026));
});
