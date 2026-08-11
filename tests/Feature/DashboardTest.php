<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard with real totals', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => now()->toDateString(),
        'amount' => '42.50',
        'description' => 'Coffee',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('totals.today', '42.50')
            ->where('totals.current_month', '42.50')
            ->where('totals.same_day_last_year', '0.00')
            ->where('comparisons.today_vs_same_day_last_year_percentage', 100)
            ->has('latest_expenses', 1)
            ->where('latest_expenses.0.description', 'Coffee')
            ->has('current_month_by_category')
            ->has('comparisons'));
});
