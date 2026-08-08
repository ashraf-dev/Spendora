<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

beforeEach(function () {
    Client::factory()->asPersonalAccessTokenClient()->create([
        'name' => 'Spendora Test',
        'provider' => 'users',
    ]);
});

test('profile information can be updated', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    Passport::actingAs($user);

    $this->putJson('/api/v1/profile', [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ])->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.email', 'new@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);
});

test('duplicate emails are rejected on profile update', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'mine@example.com']);

    Passport::actingAs($user);

    $this->putJson('/api/v1/profile', [
        'name' => $user->name,
        'email' => 'taken@example.com',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email'], 'errors');
});

test('password update requires the correct current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    Passport::actingAs($user);

    $this->putJson('/api/v1/profile/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['current_password'], 'errors');

    $this->putJson('/api/v1/profile/password', [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertOk()
        ->assertJsonPath('success', true);

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('language can only be en or ar', function () {
    $user = User::factory()->create(['language' => 'en']);

    Passport::actingAs($user);

    $this->putJson('/api/v1/profile/language', [
        'language' => 'fr',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['language'], 'errors');

    $this->putJson('/api/v1/profile/language', [
        'language' => 'ar',
    ])->assertOk()
        ->assertJsonPath('data.language', 'ar');
});

test('avatar can be uploaded and deleted', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->postJson('/api/v1/profile/avatar', [
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ])->assertOk()
        ->assertJsonPath('success', true);

    $user->refresh();
    expect($user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar);

    $this->deleteJson('/api/v1/profile/avatar')
        ->assertOk()
        ->assertJsonPath('data.avatar', null);

    expect($user->fresh()->avatar)->toBeNull();
});
