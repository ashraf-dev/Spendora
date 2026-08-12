<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('an authenticated users saved Arabic preference localizes the application shell', function () {
    $user = User::factory()->create(['language' => 'ar']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('lang="ar"', false)
        ->assertSee('dir="rtl"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('locale', 'ar')
            ->where('translations.dashboard.title', 'لوحة التحكم')
            ->where('translations.navigation.expenses', 'المصروفات'));
});

test('saving the language preference changes subsequent localized responses', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user)
        ->put(route('profile.language'), ['language' => 'ar'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->language)->toBe('ar');

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'ar')
            ->where('auth.user.language', 'ar')
            ->where('translations.profile.save_preference', 'حفظ التفضيلات'));
});

test('unsupported language preferences are rejected', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user)
        ->put(route('profile.language'), ['language' => 'fr'])
        ->assertSessionHasErrors('language');

    expect($user->refresh()->language)->toBe('en');
});
