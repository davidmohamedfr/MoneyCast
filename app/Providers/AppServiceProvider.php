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
use App\Domain\Import\Models\Import;
use App\Domain\Import\Policies\ImportPolicy;
use App\Domain\Import\Repositories\ImportRepository;
use App\Domain\Import\Repositories\ImportRepositoryInterface;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Policies\TransactionPolicy;
use App\Domain\Transaction\Repositories\TransactionRepository;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Observers\AccountObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->app->bind(ImportRepositoryInterface::class, ImportRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Import::class, ImportPolicy::class);

        // Register observers
        Account::observe(AccountObserver::class);

        // Configure rate limiters
        $this->configureRateLimiting();

        // Preserve float types (e.g., 0.0, 1500.0) in JSON encoding
        // This ensures that floats with zero decimals are not converted to integers in JSON responses
        JsonResponse::macro('setDefaultOptions', function () {
            $this->setEncodingOptions(
                JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            return $this;
        });
    }

    /**
     * Configure rate limiting for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Rate limiter for financial operations (accounts, transactions)
        // Limit: 10 requests per minute per user
        RateLimiter::for('financial', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
