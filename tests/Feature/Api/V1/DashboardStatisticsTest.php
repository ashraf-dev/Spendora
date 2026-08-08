<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

beforeEach(function () {
    Client::factory()->asPersonalAccessTokenClient()->create([
        'name' => 'Spendora Test',
        'provider' => 'users',
    ]);
});

test('dashboard totals are scoped to the authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create();
    $today = now()->toDateString();

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => $today,
        'amount' => '50.00',
    ]);
    Expense::factory()->create([
        'user_id' => $other->id,
        'category_id' => $category->id,
        'expense_date' => $today,
        'amount' => '999.00',
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonPath('data.totals.today', '50.00')
        ->assertJsonPath('data.totals.current_month', '50.00');
});

test('latest expenses are limited to five', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Expense::factory()->count(7)->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => now()->toDateString(),
    ]);

    Passport::actingAs($user);

    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk();
    expect($response->json('data.latest_expenses'))->toHaveCount(5);
});

test('monthly totals are calculated correctly across year boundaries', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => '2026-01-10',
        'amount' => '30.00',
    ]);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => '2025-12-20',
        'amount' => '20.00',
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/statistics/monthly?month=1&year=2026')
        ->assertOk()
        ->assertJsonPath('data.selected_month_total', '30.00')
        ->assertJsonPath('data.previous_month_total', '20.00')
        ->assertJsonPath('data.expense_count', 1);
});

test('same day last year comparison and zero totals do not cause division errors', function () {
    $user = User::factory()->create();

    Passport::actingAs($user);

    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.totals.today', '0.00')
        ->assertJsonPath('data.comparisons.month_over_month_percentage', 0)
        ->assertJsonPath('data.comparisons.today_vs_same_day_last_year_percentage', 0);
});

test('categories with no expenses appear with zero totals', function () {
    $user = User::factory()->create();
    $empty = Category::factory()->create(['name_en' => 'Empty']);
    $used = Category::factory()->create(['name_en' => 'Used']);

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $used->id,
        'expense_date' => now()->toDateString(),
        'amount' => '40.00',
    ]);

    Passport::actingAs($user);

    $response = $this->getJson('/api/v1/statistics/categories?month='.now()->month.'&year='.now()->year);

    $response->assertOk();

    $categories = collect($response->json('data.categories'));
    $emptyRow = $categories->firstWhere('category.id', $empty->id);
    $usedRow = $categories->firstWhere('category.id', $used->id);

    expect($emptyRow['total_amount'])->toBe('0.00')
        ->and($emptyRow['expense_count'])->toBe(0)
        ->and($emptyRow['percentage'])->toBe(0)
        ->and($usedRow['total_amount'])->toBe('40.00')
        ->and($usedRow['percentage'])->toBe(100);
});

test('monthly daily totals include zero expense days', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => '2026-02-01',
        'amount' => '10.00',
    ]);

    Passport::actingAs($user);

    $response = $this->getJson('/api/v1/statistics/monthly?month=2&year=2026');

    $response->assertOk();
    expect($response->json('data.daily_totals'))->toHaveCount(28);
    expect($response->json('data.daily_totals.0'))->toMatchArray([
        'date' => '2026-02-01',
        'total' => '10.00',
    ]);
    expect($response->json('data.daily_totals.1.total'))->toBe('0.00');
});
