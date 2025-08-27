<?php

namespace App\Providers;

use App\Models\User;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(SecurityScheme::http('bearer'));
        });

        Scramble::configure()
            ->routes(function (Route $route) {
                return Str::startsWith($route->uri(), 'api/v1');
            });

        Gate::define('viewApiDocs', function (User $user) {
            return true; //in_array($user->email, ['admin@app.com']);
        });
    }
}

    // public function boot(): void
    // {
    //     Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
    //         $openApi->secure(
    //             SecurityScheme::http('bearer')
    //         );
    //     });

    //     Gate::define('viewApiDocs', function (User $user) {
    //         return true; //in_array($user->email, ['admin@app.com']);
    //     });
    // }
