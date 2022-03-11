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
        $this->addFilters();
    }

    public function addFilters()
    {
        Eventy::addFilter('index.product.scopes', fn($scopes) => array_merge($scopes ?: [], [WithProductStockScopeMsi::class]));
        Eventy::addFilter('productpage.scopes', fn($scopes) => array_merge($scopes ?: [], [WithProductStockScopeMsi::class]));
        if (config('rapidez.msi.expose_stock_in_list')) {
            Eventy::addFilter('index.product.scopes', fn($scopes) => array_merge($scopes ?: [], [WithStockQtyScope::class]));
        }
        if (config('rapidez.msi.expose_stock_in_detail')) {
            Eventy::addFilter('productpage.scopes', fn($scopes) => array_merge($scopes ?: [], [WithStockQtyScope::class]));
        }
    }
}
