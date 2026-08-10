<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

beforeEach(function () {
    Client::factory()->asPersonalAccessTokenClient()->create([
        'name' => 'Spendora Test',
        'provider' => 'users',
    ]);
});

test('categories index returns all active categories as valid json', function () {
    $user = User::factory()->create();
    $active = Category::factory()->count(2)->create(['is_active' => true]);
    Category::factory()->inactive()->create();

    Passport::actingAs($user);

    $response = $this->getJson('/api/v1/categories');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'name_en',
                    'name_ar',
                    'icon',
                    'is_active',
                ],
            ],
        ]);

    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->toContain($active[0]->id, $active[1]->id)
        ->and($ids)->toHaveCount(2);
});

test('category show returns a category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'name_en' => 'Food',
        'name_ar' => 'طعام',
        'is_active' => true,
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/categories/'.$category->id)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $category->id)
        ->assertJsonPath('data.name_en', 'Food')
        ->assertJsonPath('data.name_ar', 'طعام')
        ->assertJsonPath('data.is_active', true);
});

test('invalid or inactive category returns 404', function () {
    $user = User::factory()->create();
    $inactive = Category::factory()->inactive()->create();

    Passport::actingAs($user);

    $this->getJson('/api/v1/categories/999999')->assertNotFound();
    $this->getJson('/api/v1/categories/'.$inactive->id)->assertNotFound();
});
