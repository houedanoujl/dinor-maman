<?php

namespace App\Providers;

use App\Support\ContestSettings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Partage la date de fin du concours et son état avec toutes les vues
        View::composer('*', function ($view) {
            try {
                $endsAt = ContestSettings::endsAt();
                $uploadEndsAt = ContestSettings::uploadEndsAt();
                $view->with([
                    'contestEndsAt' => $endsAt->toDateTimeString(),
                    'contestEnded' => ContestSettings::isEnded(),
                    'uploadEndsAt' => $uploadEndsAt->toDateTimeString(),
                    'uploadPhase' => ContestSettings::isUploadPhase(),
                    'votePhase' => ContestSettings::isVotePhase(),
                    'hasSeparateVotePhase' => ! $uploadEndsAt->equalTo($endsAt),
                ]);
            } catch (\Throwable $e) {
                $view->with([
                    'contestEndsAt' => (string) config('contest.ends_at'),
                    'contestEnded' => false,
                    'uploadEndsAt' => (string) config('contest.ends_at'),
                    'uploadPhase' => true,
                    'votePhase' => false,
                    'hasSeparateVotePhase' => false,
                ]);
            }
        });
    }
}
