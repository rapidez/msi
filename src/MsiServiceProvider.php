<?php

namespace Rapidez\Msi;

use Illuminate\Support\ServiceProvider;
use Rapidez\Msi\Http\Middleware\DetermineAndSetStockId;
use Rapidez\Msi\Models\Scopes\Product\WithProductStockScopeMsi;
use Rapidez\Msi\Models\Scopes\Product\WithStockQtyScope;
use TorMorten\Eventy\Facades\Eventy;

class MsiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', DetermineAndSetStockId::class);
    }

    public function boot()
    {
        Eventy::addFilter('product.scopes', fn($scopes) => array_merge($scopes ?: [], [WithProductStockScopeMsi::class]));

        if (config('rapidez.expose_stock')) {
            Eventy::addFilter('product.scopes', fn($scopes) => array_merge($scopes ?: [], [WithStockQtyScope::class]));
        }
    }
}
