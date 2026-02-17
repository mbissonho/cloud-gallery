<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Aws\Sqs\SqsClient;
use App\Scout\OpenSearch\Engine as AppOpenSearchEngine;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SqsClient::class, function ($app) {
            $config = $app->make('config')->get('queue.connections.sqs');

            $awsConfig = [
                'region' => $config['region'],
                'version' => 'latest',
                'credentials' => [
                    'key'    => $config['key'],
                    'secret' => $config['secret'],
                ]
            ];

            if (!empty($config['prefix'])) {
                $awsConfig['endpoint'] = $config['prefix'];
            }

            return new SqsClient($awsConfig);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        $this->app[EngineManager::class]->extend('opensearch', function () {
            return new AppOpenSearchEngine(config('scout.soft_delete'));
        });

        User::observe(UserObserver::class);
    }
}
