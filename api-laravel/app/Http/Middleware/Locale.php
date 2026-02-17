<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class Locale
{
    private array $supportedLocales = [
        'en',
        'pt_BR'
    ];


    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestedLanguage = $request->header('lang') ?? $request->input('lang', 'en');
        if(in_array($requestedLanguage, $this->supportedLocales))
        {
            App::setLocale($requestedLanguage);
        }

        return $next($request);
    }
}
