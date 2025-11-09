<?php

namespace App\Providers;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Policies\AccountPolicy;
use App\Domain\Account\Repositories\AccountRepository;
use App\Domain\Account\Repositories\AccountRepositoryInterface;
use App\Domain\Category\Policies\CategoryPolicy;
use App\Domain\Category\Models\Category;
use App\Domain\Category\Repositories\CategoryRepository;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);
    }
}
