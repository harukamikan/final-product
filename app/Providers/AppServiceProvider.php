<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use SocialiteProviders\Manager\SocialiteWasCalled;
use App\Models\RewardDistribution;

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

            // ======================
            // 背景色（既存）
            // ======================
            $bgColor = config('app.default_background_color');

            if (Auth::check() && Auth::user()->background_color) {
                $bgColor = Auth::user()->background_color;
            }

            // ======================
            // 🎰 ガチャ判定（追加）
            // ======================
            $hasActiveGachaReward = false;

            if (Auth::check() && Auth::user()->company_id) {
                $hasActiveGachaReward = RewardDistribution::where('company_id', Auth::user()->company_id)
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')
                            ->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('ends_at')
                            ->orWhere('ends_at', '>=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('quantity')
                            ->orWhere('quantity', '>', 0);
                    })
                    ->exists();
            }

            $view->with([
                'bgColor'              => $bgColor,
                'hasActiveGachaReward' => $hasActiveGachaReward,
            ]);
        });
    }
}
