<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

beforeEach(function () {
    Client::factory()->asPersonalAccessTokenClient()->create([
        'name' => 'Spendora Test',
        'provider' => 'users',
    ]);
});

test('registration succeeds', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'API User',
        'email' => 'api@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'language' => 'ar',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'api@example.com')
        ->assertJsonPath('data.user.language', 'ar')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonStructure(['data' => ['access_token', 'user']]);

    $this->assertDatabaseHas('users', [
        'email' => 'api@example.com',
        'language' => 'ar',
    ]);
});

test('duplicate email is rejected', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'API User',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors(['email'], 'errors');
});

test('login succeeds with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonStructure(['data' => ['access_token', 'token_type']]);
});

test('login fails with incorrect credentials', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'wrong-password',
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', __('api.auth.invalid_credentials'));
});

test('protected routes reject unauthenticated requests', function () {
    $this->getJson('/api/v1/auth/user')->assertUnauthorized();
});

test('authenticated user endpoint returns the current user', function () {
    $user = User::factory()->create();

    Passport::actingAs($user);

    $this->getJson('/api/v1/auth/user')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

test('logout revokes the current token', function () {
    $user = User::factory()->create();
    $tokenResult = $user->createToken('flutter');
    $accessToken = $tokenResult->accessToken;
    $tokenId = $tokenResult->accessTokenId;

    $this->withToken($accessToken)
        ->postJson('/api/v1/auth/logout')
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('oauth_access_tokens', [
        'id' => $tokenId,
        'revoked' => true,
    ]);

    $this->withToken($accessToken)
        ->getJson('/api/v1/auth/user')
        ->assertUnauthorized();
});
