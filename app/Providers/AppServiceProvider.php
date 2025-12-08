<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Slack\SlackExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->events->listen(
            SocialiteWasCalled::class,
            SlackExtendSocialite::class.'@handle'
        );
    }
}
