<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->app->events->listen(
            SocialiteWasCalled::class,
            'SocialiteProviders\\Slack\\SlackExtendSocialite@handle'
        );

        View::composer('*', function ($view) {
            $bgColor = config('app.default_background_color');

            if (Auth::check() && Auth::user()->background_color) {
                $bgColor = Auth::user()->background_color;
            }

            $view->with('bgColor', $bgColor);
        });
    }
}
