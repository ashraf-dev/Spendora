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

test('profile endpoint returns the current profile with expected fields', function () {
    $user = User::factory()->create([
        'name' => 'Spendora User',
        'email' => 'profile@example.com',
        'language' => 'en',
    ]);

    Passport::actingAs($user);

    $this->getJson('/api/v1/profile')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', 'Spendora User')
        ->assertJsonPath('data.email', 'profile@example.com')
        ->assertJsonPath('data.language', 'en')
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'avatar',
                'language',
                'email_verified_at',
                'created_at',
                'updated_at',
            ],
        ]);
});

test('profile information can be updated', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'email_verified_at' => now(),
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
        'email_verified_at' => null,
    ]);
});

test('profile update validation errors are returned', function () {
    $user = User::factory()->create();

    Passport::actingAs($user);

    $this->putJson('/api/v1/profile', [
        'name' => '',
        'email' => 'not-an-email',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email'], 'errors');
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

test('password update validation errors are returned', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    Passport::actingAs($user);

    $this->putJson('/api/v1/profile/password', [
        'current_password' => 'password',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['password'], 'errors');
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

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'language' => 'ar',
    ]);
});

test('avatar can be uploaded', function () {
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
});

test('invalid avatar uploads are rejected', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->postJson('/api/v1/profile/avatar', [
        'avatar' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['avatar'], 'errors');

    $this->postJson('/api/v1/profile/avatar', [
        'avatar' => UploadedFile::fake()->image('too-large.jpg')->size(3000),
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['avatar'], 'errors');
});

test('avatar can be deleted and missing avatar is handled safely', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->deleteJson('/api/v1/profile/avatar')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.avatar', null);

    $this->postJson('/api/v1/profile/avatar', [
        'avatar' => UploadedFile::fake()->image('avatar.png'),
    ])->assertOk();

    $user->refresh();
    $path = $user->avatar;
    Storage::disk('public')->assertExists($path);

    $this->deleteJson('/api/v1/profile/avatar')
        ->assertOk()
        ->assertJsonPath('data.avatar', null);

    expect($user->fresh()->avatar)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});
