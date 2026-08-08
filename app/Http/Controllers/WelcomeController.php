<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function __invoke(?string $locale = null): Response
    {
        App::setLocale($locale ?? 'en');

        $copy = __('welcome');
        $copy['footer']['copyright'] = __('welcome.footer.copyright', [
            'year' => now()->year,
        ]);

        return Inertia::render('Welcome', [
            'locale' => App::currentLocale(),
            'copy' => $copy,
        ]);
    }
}
