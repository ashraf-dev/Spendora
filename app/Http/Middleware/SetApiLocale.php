<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    /**
     * Supported API locales.
     *
     * @var list<string>
     */
    private const SUPPORTED = ['en', 'ar'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $header = $request->header('Accept-Language');

        if (is_string($header) && $header !== '') {
            $preferred = strtolower(substr(trim(explode(',', $header)[0]), 0, 2));

            if (in_array($preferred, self::SUPPORTED, true)) {
                return $preferred;
            }
        }

        $user = $request->user('api') ?? $request->user();

        if ($user !== null && in_array($user->language ?? null, self::SUPPORTED, true)) {
            return $user->language;
        }

        $fallback = config('app.locale', 'en');

        return in_array($fallback, self::SUPPORTED, true) ? $fallback : 'en';
    }
}
