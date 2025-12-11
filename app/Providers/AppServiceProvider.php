<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot()
    {
        // 本番環境ではHTTPSを強制
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->app->events->listen(
            SocialiteWasCalled::class,
            'SocialiteProviders\\Slack\\SlackExtendSocialite@handle'
        );
    }
}