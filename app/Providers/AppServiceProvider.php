<?php

namespace App\Providers;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Policies\AccountPolicy;
use App\Domain\Account\Repositories\AccountRepository;
use App\Domain\Account\Repositories\AccountRepositoryInterface;
use App\Domain\Category\Models\Category;
use App\Domain\Category\Policies\CategoryPolicy;
use App\Domain\Category\Repositories\CategoryRepository;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Policies\TransactionPolicy;
use App\Domain\Transaction\Repositories\TransactionRepository;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Observers\AccountObserver;
use Illuminate\Http\JsonResponse;
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
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);

        // Register observers
        Account::observe(AccountObserver::class);

        // Preserve float types (e.g., 0.0, 1500.0) in JSON encoding
        // This ensures that floats with zero decimals are not converted to integers in JSON responses
        JsonResponse::macro('setDefaultOptions', function () {
            $this->setEncodingOptions(
                JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            return $this;
        });
    }
}
