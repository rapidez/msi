<?php

namespace Rapidez\Msi;

use Illuminate\Support\ServiceProvider;
use Rapidez\Core\Models\Product;
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

        Eventy::addFilter('product.children.select', function ($select) {
            $stockId = config('rapidez.stock_id');
            // Replace the default "in_stock" with the MSI value.
            return str_replace('"in_stock", children_stock.is_in_stock,'.PHP_EOL, '', $select).PHP_EOL.',"in_stock", (
                SELECT is_salable
                FROM inventory_stock_' . $stockId . '
                WHERE inventory_stock_' . $stockId . '.sku = '.(new Product())->getTable().'.sku
            )';
        });

        if (config('rapidez.expose_stock')) {
            Eventy::addFilter('product.scopes', fn($scopes) => array_merge($scopes ?: [], [WithStockQtyScope::class]));

            Eventy::addFilter('product.children.select', function ($select) {
                $stockId = config('rapidez.stock_id');
                // Replace the default "qty" with the MSI value.
                return str_replace('"qty", children_stock.qty,'.PHP_EOL, '', $select).PHP_EOL.',"qty", (
                    SELECT inventory_stock_' . $stockId . '.quantity + COALESCE(SUM(inventory_reservation.quantity),0)
                    FROM inventory_stock_' . $stockId . '
                    LEFT JOIN inventory_reservation ON inventory_stock_' . $stockId . '.sku = inventory_reservation.sku
                    WHERE inventory_stock_' . $stockId . '.sku = children.sku
                    GROUP BY inventory_reservation.sku
                )';
            });
        }
    }
}
