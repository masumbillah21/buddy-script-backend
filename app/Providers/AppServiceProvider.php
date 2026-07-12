<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\Eloquent\UserRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\PostRepositoryInterface::class,
            \App\Repositories\Eloquent\PostRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\CommentRepositoryInterface::class,
            \App\Repositories\Eloquent\CommentRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\ReactionRepositoryInterface::class,
            \App\Repositories\Eloquent\ReactionRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
