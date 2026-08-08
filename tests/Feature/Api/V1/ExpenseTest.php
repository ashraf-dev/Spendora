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

test('a user can create an expense', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Passport::actingAs($user);

    $response = $this->postJson('/api/v1/expenses', [
        'category_id' => $category->id,
        'expense_date' => '2026-08-01',
        'amount' => '25.50',
        'description' => 'Lunch',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.amount', '25.50')
        ->assertJsonPath('data.description', 'Lunch');

    $this->assertDatabaseHas('expenses', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => '25.50',
    ]);
});

test('invalid expense data is rejected', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->postJson('/api/v1/expenses', [
        'category_id' => 999,
        'expense_date' => 'not-a-date',
        'amount' => 0,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id', 'expense_date', 'amount'], 'errors');
});

test('a user can list their own expenses', function () {
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

    $response = $this->getJson('/api/v1/expenses');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.meta.total', 2);
});

test('expense filters work correctly', function () {
    $user = User::factory()->create();
    $food = Category::factory()->create(['name_en' => 'Food']);
    $travel = Category::factory()->create(['name_en' => 'Travel']);

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $food->id,
        'expense_date' => '2026-08-01',
        'description' => 'Burger',
        'amount' => 10,
    ]);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $travel->id,
        'expense_date' => '2026-07-15',
        'description' => 'Flight',
        'amount' => 200,
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/expenses?category_id='.$food->id)
        ->assertOk()
        ->assertJsonPath('data.meta.total', 1)
        ->assertJsonPath('data.data.0.description', 'Burger');

    $this->getJson('/api/v1/expenses?month=8&year=2026')
        ->assertOk()
        ->assertJsonPath('data.meta.total', 1);

    $this->getJson('/api/v1/expenses?search=Flight')
        ->assertOk()
        ->assertJsonPath('data.meta.total', 1)
        ->assertJsonPath('data.data.0.description', 'Flight');
});

test('a user can view update and delete their own expense', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $expense = Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => '10.00',
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/expenses/'.$expense->id)
        ->assertOk()
        ->assertJsonPath('data.id', $expense->id);

    $this->putJson('/api/v1/expenses/'.$expense->id, [
        'amount' => '15.75',
        'description' => 'Updated',
    ])->assertOk()
        ->assertJsonPath('data.amount', '15.75')
        ->assertJsonPath('data.description', 'Updated');

    $this->deleteJson('/api/v1/expenses/'.$expense->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
});

test('a user cannot access another users expense', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $category = Category::factory()->create();
    $expense = Expense::factory()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
    ]);

    Passport::actingAs($intruder);

    $this->getJson('/api/v1/expenses/'.$expense->id)->assertNotFound();
    $this->putJson('/api/v1/expenses/'.$expense->id, ['amount' => '99.00'])->assertNotFound();
    $this->deleteJson('/api/v1/expenses/'.$expense->id)->assertNotFound();
});
