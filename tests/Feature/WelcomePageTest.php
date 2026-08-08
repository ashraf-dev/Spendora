<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the english welcome page is the default', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('locale', 'en')
            ->where('copy.hero.title_accent', 'money goes')
        );
});

test('the arabic welcome page uses arabic translations and direction', function () {
    $this->get(route('home', ['locale' => 'ar']))
        ->assertOk()
        ->assertSee('lang="ar"', false)
        ->assertSee('dir="rtl"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('locale', 'ar')
            ->where('copy.hero.title_accent', 'أموالك')
            ->where('copy.language.current', 'العربية')
        );
});

test('unsupported welcome page locales are not accepted', function () {
    $this->get('/fr')->assertNotFound();
});
