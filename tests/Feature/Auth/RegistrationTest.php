<?php

use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
            ->has('passwordRules')
            ->where('locale', 'en')
            ->where('copy.register.title', 'Create an account')
        );
});

test('registration screen can be rendered in arabic', function () {
    $this->get(route('register', ['locale' => 'ar']))
        ->assertOk()
        ->assertSee('lang="ar"', false)
        ->assertSee('dir="rtl"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
            ->where('locale', 'ar')
            ->where('copy.register.title', 'إنشاء حساب جديد')
            ->where('copy.register.submit', 'إنشاء الحساب')
        );
});

test('registration validation errors are translated to arabic', function () {
    $this->post(route('register.store', ['locale' => 'ar']), [])
        ->assertSessionHasErrors([
            'name' => 'حقل الاسم مطلوب.',
            'email' => 'حقل البريد الإلكتروني مطلوب.',
            'password' => 'حقل كلمة المرور مطلوب.',
        ]);
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
