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

test('monthly statistics return expected totals and exclude other users', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
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
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => '2026-03-01',
        'amount' => '40.00',
    ]);
    Expense::factory()->create([
        'user_id' => $other->id,
        'category_id' => $category->id,
        'expense_date' => '2026-01-15',
        'amount' => '999.00',
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/statistics/monthly?month=1&year=2026')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.selected_month_total', '30.00')
        ->assertJsonPath('data.previous_month_total', '20.00')
        ->assertJsonPath('data.current_year_total', '70.00')
        ->assertJsonPath('data.expense_count', 1)
        ->assertJsonPath('data.highest_expense', '30.00')
        ->assertJsonPath('data.average_expense', '30.00')
        ->assertJsonPath('data.category_totals.0.category.id', $category->id)
        ->assertJsonPath('data.category_totals.0.total_amount', '30.00')
        ->assertJsonPath('data.category_totals.0.expense_count', 1);
});

test('monthly statistics include every day even when it has no expenses', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => '2026-02-10',
        'amount' => '12.50',
    ]);

    Passport::actingAs($user);

    $response = $this->getJson('/api/v1/statistics/monthly?month=2&year=2026')
        ->assertOk();

    $dailyTotals = collect($response->json('data.daily_totals'));

    expect($dailyTotals)->toHaveCount(28)
        ->and($dailyTotals->firstWhere('date', '2026-02-01')['total'])->toBe('0.00')
        ->and($dailyTotals->firstWhere('date', '2026-02-10')['total'])->toBe('12.50')
        ->and($dailyTotals->last())->toMatchArray([
            'date' => '2026-02-28',
            'total' => '0.00',
        ]);
});

test('category statistics return totals grouped by category and exclude other users', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $food = Category::factory()->create(['name_en' => 'Food']);
    $travel = Category::factory()->create(['name_en' => 'Travel']);
    $empty = Category::factory()->create(['name_en' => 'Empty']);

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $food->id,
        'expense_date' => '2026-08-01',
        'amount' => '40.00',
    ]);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $travel->id,
        'expense_date' => '2026-08-02',
        'amount' => '60.00',
    ]);
    Expense::factory()->create([
        'user_id' => $other->id,
        'category_id' => $food->id,
        'expense_date' => '2026-08-01',
        'amount' => '999.00',
    ]);

    Passport::actingAs($user);

    $response = $this->getJson('/api/v1/statistics/categories?month=8&year=2026');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.month_total', '100.00');

    $categories = collect($response->json('data.categories'));
    $foodRow = $categories->firstWhere('category.id', $food->id);
    $travelRow = $categories->firstWhere('category.id', $travel->id);
    $emptyRow = $categories->firstWhere('category.id', $empty->id);

    expect($foodRow['total_amount'])->toBe('40.00')
        ->and($foodRow['percentage'])->toBe(40)
        ->and($travelRow['total_amount'])->toBe('60.00')
        ->and($travelRow['percentage'])->toBe(60)
        ->and($emptyRow['total_amount'])->toBe('0.00')
        ->and($emptyRow['expense_count'])->toBe(0)
        ->and($emptyRow['percentage'])->toBe(0);
});

test('category detail statistics support filtering and exclude other users', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create();
    $otherCategory = Category::factory()->create();

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => '2026-08-05',
        'amount' => '25.00',
        'description' => 'Mine August',
    ]);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => '2026-07-05',
        'amount' => '10.00',
        'description' => 'Mine July',
    ]);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $otherCategory->id,
        'expense_date' => '2026-08-05',
        'amount' => '50.00',
    ]);
    Expense::factory()->create([
        'user_id' => $other->id,
        'category_id' => $category->id,
        'expense_date' => '2026-08-05',
        'amount' => '999.00',
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/statistics/categories/'.$category->id.'?month=8&year=2026')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.category.id', $category->id)
        ->assertJsonPath('data.month', 8)
        ->assertJsonPath('data.year', 2026)
        ->assertJsonPath('data.selected_month_total', '25.00')
        ->assertJsonPath('data.expense_count', 1)
        ->assertJsonPath('data.expenses.meta.total', 1)
        ->assertJsonPath('data.expenses.data.0.description', 'Mine August')
        ->assertJsonStructure([
            'data' => [
                'navigation' => [
                    'previous_month' => ['month', 'year'],
                    'next_month' => ['month', 'year'],
                ],
            ],
        ]);
});

test('invalid or inactive category statistics return 404', function () {
    $user = User::factory()->create();
    $inactive = Category::factory()->inactive()->create();

    Passport::actingAs($user);

    $this->getJson('/api/v1/statistics/categories/999999')->assertNotFound();
    $this->getJson('/api/v1/statistics/categories/'.$inactive->id.'?month=8&year=2026')
        ->assertNotFound();
});

test('statistics query parameters are validated', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Passport::actingAs($user);

    $this->getJson('/api/v1/statistics/monthly?month=13&year=1999')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['month', 'year'], 'errors');

    $this->getJson('/api/v1/statistics/categories/'.$category->id.'?per_page=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['per_page'], 'errors');
});
