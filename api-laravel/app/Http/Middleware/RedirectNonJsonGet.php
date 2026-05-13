<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectNonJsonGet
{
    public function handle(Request $request, Closure $next): Response
    {
        // verify-email/* is hit directly from the browser (link in an email),
        // so it never carries an Accept: application/json header. Allow it
        // through so the EmailVerificationRequest can run and the user can
        // be redirected to the SPA's verified=1 dashboard.
        if (
            $request->isMethod('GET')
            && ! $request->expectsJson()
            && ! $request->is('/', 'up', 'verify-email/*', 'sanctum/csrf-cookie', 'user')
        ) {
            return redirect('/');
        }

        return $next($request);
    }
}
