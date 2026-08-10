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

test('dashboard returns latest five expenses for the authenticated user only', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create();

    $expenses = Expense::factory()->count(7)->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => now()->toDateString(),
    ]);
    Expense::factory()->count(3)->create([
        'user_id' => $other->id,
        'category_id' => $category->id,
        'expense_date' => now()->toDateString(),
        'amount' => '999.00',
    ]);

    Passport::actingAs($user);

    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonPath('success', true);

    $latest = $response->json('data.latest_expenses');
    $expectedIds = $expenses->sortByDesc('id')->take(5)->pluck('id')->values()->all();

    expect($latest)->toHaveCount(5)
        ->and(collect($latest)->pluck('id')->all())->toBe($expectedIds);

    foreach ($latest as $expense) {
        expect($expense['amount'])->not->toBe('999.00');
    }
});

test('dashboard returns expected totals and comparisons scoped to the user', function () {
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
        ->assertJsonPath('data.totals.current_month', '50.00')
        ->assertJsonPath('data.current_month_by_category.0.category.id', $category->id)
        ->assertJsonPath('data.current_month_by_category.0.total_amount', '50.00')
        ->assertJsonPath('data.current_month_by_category.0.expense_count', 1)
        ->assertJsonStructure([
            'data' => [
                'latest_expenses',
                'totals' => [
                    'today',
                    'current_month',
                    'previous_month',
                    'current_year',
                    'same_day_last_year',
                ],
                'comparisons' => [
                    'month_over_month_percentage',
                    'today_vs_same_day_last_year_percentage',
                ],
                'current_month_by_category',
            ],
        ]);
});

test('dashboard comparisons are safe when totals are zero', function () {
    $user = User::factory()->create();

    Passport::actingAs($user);

    $this->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonPath('data.totals.today', '0.00')
        ->assertJsonPath('data.comparisons.month_over_month_percentage', 0)
        ->assertJsonPath('data.comparisons.today_vs_same_day_last_year_percentage', 0);
});
