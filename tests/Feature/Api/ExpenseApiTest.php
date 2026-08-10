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

test('expenses index returns only the authenticated users expenses', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create();

    Expense::factory()->count(2)->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);
    Expense::factory()->create([
        'user_id' => $other->id,
        'category_id' => $category->id,
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/expenses')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.meta.total', 2);
});

test('expenses index returns an empty list when none exist', function () {
    $user = User::factory()->create();

    Passport::actingAs($user);

    $this->getJson('/api/v1/expenses')
        ->assertOk()
        ->assertJsonPath('data.meta.total', 0)
        ->assertJsonPath('data.data', []);
});

test('expenses index supports every documented filter', function () {
    $user = User::factory()->create();
    $food = Category::factory()->create();
    $travel = Category::factory()->create();

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $food->id,
        'expense_date' => '2026-08-05',
        'amount' => '10.00',
        'description' => 'Team lunch',
    ]);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $travel->id,
        'expense_date' => '2026-08-20',
        'amount' => '200.00',
        'description' => 'Train ticket',
    ]);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $food->id,
        'expense_date' => '2026-07-15',
        'amount' => '30.00',
        'description' => 'Client lunch',
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/expenses?category_id='.$food->id)
        ->assertOk()
        ->assertJsonPath('data.meta.total', 2);

    $this->getJson('/api/v1/expenses?month=8&year=2026')
        ->assertOk()
        ->assertJsonPath('data.meta.total', 2);

    $this->getJson('/api/v1/expenses?date_from=2026-08-10&date_to=2026-08-31')
        ->assertOk()
        ->assertJsonPath('data.meta.total', 1)
        ->assertJsonPath('data.data.0.description', 'Train ticket');

    $this->getJson('/api/v1/expenses?search=lunch')
        ->assertOk()
        ->assertJsonPath('data.meta.total', 2);
});

test('expenses index supports pagination and sorting and validates query parameters', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => '2026-08-01',
        'amount' => '30.00',
    ]);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'expense_date' => '2026-08-02',
        'amount' => '10.00',
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/expenses?sort=amount&direction=asc&per_page=1')
        ->assertOk()
        ->assertJsonPath('data.data.0.amount', '10.00')
        ->assertJsonPath('data.meta.per_page', 1)
        ->assertJsonPath('data.meta.total', 2);

    $this->getJson('/api/v1/expenses?month=13&year=1999&per_page=101&sort=id&direction=sideways&date_from=2026-08-10&date_to=2026-08-01')
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'month',
            'year',
            'per_page',
            'sort',
            'direction',
            'date_to',
        ], 'errors');
});

test('a user can create an expense', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create();

    Passport::actingAs($user);

    $response = $this->postJson('/api/v1/expenses', [
        'category_id' => $category->id,
        'expense_date' => '2026-08-01',
        'amount' => '25.50',
        'description' => 'Lunch',
        'user_id' => $other->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.amount', '25.50')
        ->assertJsonPath('data.description', 'Lunch')
        ->assertJsonPath('data.category_id', $category->id);

    $this->assertDatabaseHas('expenses', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => '25.50',
        'description' => 'Lunch',
    ]);

    $this->assertDatabaseMissing('expenses', [
        'user_id' => $other->id,
        'category_id' => $category->id,
        'description' => 'Lunch',
    ]);
});

test('expense creation validates required fields', function () {
    $user = User::factory()->create();

    Passport::actingAs($user);

    $this->postJson('/api/v1/expenses', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id', 'expense_date', 'amount'], 'errors');
});

test('expense creation rejects invalid category and negative amounts', function () {
    $user = User::factory()->create();
    $inactive = Category::factory()->inactive()->create();

    Passport::actingAs($user);

    $this->postJson('/api/v1/expenses', [
        'category_id' => 999999,
        'expense_date' => '2026-08-01',
        'amount' => '10.00',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id'], 'errors');

    $this->postJson('/api/v1/expenses', [
        'category_id' => $inactive->id,
        'expense_date' => '2026-08-01',
        'amount' => '10.00',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id'], 'errors');

    $this->postJson('/api/v1/expenses', [
        'category_id' => Category::factory()->create()->id,
        'expense_date' => '2026-08-01',
        'amount' => '-5.00',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['amount'], 'errors');

    $this->postJson('/api/v1/expenses', [
        'category_id' => Category::factory()->create()->id,
        'expense_date' => '2026-08-01',
        'amount' => '0',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['amount'], 'errors');
});

test('a user can view their own expense', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $expense = Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => '12.34',
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/expenses/'.$expense->id)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $expense->id)
        ->assertJsonPath('data.amount', '12.34');
});

test('a user cannot access another users expense and missing expense returns 404', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $category = Category::factory()->create();
    $expense = Expense::factory()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
    ]);

    Passport::actingAs($intruder);

    $this->getJson('/api/v1/expenses/'.$expense->id)->assertNotFound();
    $this->getJson('/api/v1/expenses/999999')->assertNotFound();
});

test('a user can update their own expense', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $expense = Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => '10.00',
        'description' => 'Old',
    ]);

    Passport::actingAs($user);

    $this->putJson('/api/v1/expenses/'.$expense->id, [
        'amount' => '15.75',
        'description' => 'Updated',
    ])->assertOk()
        ->assertJsonPath('data.amount', '15.75')
        ->assertJsonPath('data.description', 'Updated');

    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
        'amount' => '15.75',
        'description' => 'Updated',
    ]);
});

test('expense update validates input', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create([
        'user_id' => $user->id,
    ]);

    Passport::actingAs($user);

    $this->putJson('/api/v1/expenses/'.$expense->id, [
        'category_id' => 999999,
        'amount' => '-1',
        'expense_date' => 'not-a-date',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id', 'amount', 'expense_date'], 'errors');
});

test('a user cannot update another users expense and missing expense returns 404', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $expense = Expense::factory()->create([
        'user_id' => $owner->id,
    ]);

    Passport::actingAs($intruder);

    $this->putJson('/api/v1/expenses/'.$expense->id, [
        'amount' => '99.00',
    ])->assertNotFound();

    $this->putJson('/api/v1/expenses/999999', [
        'amount' => '99.00',
    ])->assertNotFound();
});

test('a user can delete their own expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create([
        'user_id' => $user->id,
    ]);

    Passport::actingAs($user);

    $this->deleteJson('/api/v1/expenses/'.$expense->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
});

test('a user cannot delete another users expense and missing expense returns 404', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $expense = Expense::factory()->create([
        'user_id' => $owner->id,
    ]);

    Passport::actingAs($intruder);

    $this->deleteJson('/api/v1/expenses/'.$expense->id)->assertNotFound();
    $this->deleteJson('/api/v1/expenses/999999')->assertNotFound();

    $this->assertDatabaseHas('expenses', ['id' => $expense->id]);
});
