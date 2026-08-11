<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;

test('guests cannot view expenses', function () {
    $this->get(route('expenses.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can list their expenses', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create([
        'name_en' => 'Food',
        'is_active' => true,
    ]);

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'description' => 'Mine',
        'amount' => '10.00',
    ]);
    Expense::factory()->create([
        'user_id' => $other->id,
        'category_id' => $category->id,
        'description' => 'Theirs',
        'amount' => '99.00',
    ]);

    $this->actingAs($user)
        ->get(route('expenses.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('expenses/Index')
            ->has('expenses.data', 1)
            ->where('expenses.data.0.description', 'Mine')
            ->where('expenses.data.0.category.name', 'Food')
            ->where('total_amount', '10.00')
            ->has('categories'));
});

test('authenticated users can create an expense', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    $this->actingAs($user)
        ->post(route('expenses.store'), [
            'category_id' => $category->id,
            'expense_date' => '2026-08-06',
            'amount' => '25.50',
            'description' => 'Lunch',
        ])
        ->assertRedirect(route('expenses.index'));

    $this->assertDatabaseHas('expenses', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'description' => 'Lunch',
        'amount' => '25.50',
    ]);
});

test('authenticated users can update their expense', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $expense = Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => '10.00',
    ]);

    $this->actingAs($user)
        ->put(route('expenses.update', $expense), [
            'amount' => '35.75',
            'description' => 'Updated lunch',
        ])
        ->assertRedirect(route('expenses.index'));

    expect($expense->fresh()->amount)->toBe('35.75')
        ->and($expense->fresh()->description)->toBe('Updated lunch');
});

test('authenticated users can delete their expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete(route('expenses.destroy', $expense))
        ->assertRedirect(route('expenses.index'));

    $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
});

test('users cannot edit another users expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create();

    $this->actingAs($user)
        ->get(route('expenses.edit', $expense))
        ->assertNotFound();
});

test('expense create validation errors are returned', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('expenses.create'))
        ->post(route('expenses.store'), [
            'category_id' => 999999,
            'expense_date' => 'not-a-date',
            'amount' => -1,
        ])
        ->assertRedirect(route('expenses.create'))
        ->assertSessionHasErrors(['category_id', 'expense_date', 'amount']);
});
