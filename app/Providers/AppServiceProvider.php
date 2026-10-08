<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Policies\ClientPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\ProductPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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
        // The app ships Bootstrap 5 (not Tailwind) — render links() with
        // Bootstrap markup so .pagination/.page-link styles apply.
        Paginator::useBootstrapFive();

        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
    }
}
