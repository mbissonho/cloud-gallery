<?php

use App\Exceptions\PaymentGatewayException;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\RedirectNonJsonGet;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api_v1.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(RedirectNonJsonGet::class);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => EnsureEmailIsVerified::class,
        ]);

        // Registration creates an account before any session exists, so there is
        // no CSRF token to validate against. Skip the check on this route only.
        $middleware->validateCsrfTokens(except: [
            'api/v1/auth/register'
        ]);

        $middleware
            ->append(\App\Http\Middleware\Locale::class)
            ->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request){
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => trans('http.404')
                ], 404);
            }
        });

        $exceptions->render(function (PaymentGatewayException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => trans('checkout.gateway_unavailable'),
                ], 503);
            }
        });
    })->create();
