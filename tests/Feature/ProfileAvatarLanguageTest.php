<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('user can update language preference', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user)
        ->put(route('profile.language'), [
            'language' => 'ar',
        ])
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->language)->toBe('ar');
});

test('user can upload and delete avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create(['avatar' => null]);

    $file = UploadedFile::fake()->image('avatar.jpg');

    $this->actingAs($user)
        ->post(route('profile.avatar.upload'), [
            'avatar' => $file,
        ])
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar);

    $this->actingAs($user)
        ->delete(route('profile.avatar.destroy'))
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->avatar)->toBeNull();
});
