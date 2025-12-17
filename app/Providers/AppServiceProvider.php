<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AuthService;
use App\Services\AccountService;
use App\Services\TransactionService;
use App\Services\NotificationService;
use App\Repositories\AccountRepository;
use App\Repositories\TransactionRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services
        $this->app->singleton(AuthService::class);
        $this->app->singleton(AccountService::class);
        $this->app->singleton(TransactionService::class);
        $this->app->singleton(NotificationService::class);
        
        // Register repositories
        $this->app->singleton(AccountRepository::class);
        $this->app->singleton(TransactionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
