<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;

test('authenticated users can view categories with monthly totals', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => now()->toDateString(),
        'amount' => '40.00',
        'description' => 'Team lunch',
    ]);

    $this->actingAs($user)
        ->get(route('categories.index', [
            'month' => now()->month,
            'year' => now()->year,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('categories/Index')
            ->where('month_total', '40.00')
            ->has('categories')
            ->where('categories.0.percentage', 100)
            ->where('categories.0.recent_expenses.0.description', 'Team lunch')
            ->has('navigation.previous_month')
            ->has('navigation.next_month'));
});

test('authenticated users can view category details', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => now()->toDateString(),
        'amount' => '12.00',
        'description' => 'Snack',
    ]);

    $this->actingAs($user)
        ->get(route('categories.show', [
            'category' => $category,
            'month' => now()->month,
            'year' => now()->year,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('categories/Show')
            ->where('selected_month_total', '12.00')
            ->where('expense_count', 1)
            ->has('expenses.data', 1)
            ->where('expenses.data.0.description', 'Snack'));
});

test('inactive categories return not found', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => false]);

    $this->actingAs($user)
        ->get(route('categories.show', $category))
        ->assertNotFound();
});
