<?php

namespace Rapidez\Msi\Models\Scopes\Product;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WithProductStockScopeMsi implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $stockId = config()->has('rapidez.stock_id') ? config('rapidez.stock_id') : $this->getInventoryStockId();
        $builder
            ->selectRaw('ANY_VALUE(inventory_stock_' . $stockId . '.is_salable) AS in_stock')
            ->leftJoin('inventory_stock_' . $stockId, $model->getTable() . '.sku', '=', 'inventory_stock_' . $stockId . '.sku');
    }

    /**
     * Used primaraly as fallback when Global Scopes are used on the Rapidez Indexer as stock_id variable set in HTTP Middleware is not available
     * @return int
     */
    public function getInventoryStockId()
    {
        $stockId = DB::table('inventory_stock_sales_channel')
            ->where('inventory_stock_sales_channel.type', 'website')
            ->where('inventory_stock_sales_channel.code', '=', function ($query) {
                $query
                    ->selectRaw('sw.code')
                    ->from('store_website as sw')
                    ->where('sw.website_id', '=', function ($query) {
                        $query
                            ->selectRaw('s.website_id')
                            ->from('store as s')
                            ->where('s.store_id', config('rapidez.store'));
                    })
                    ->limit(1);
            })
            ->pluck('stock_id')
            ->first();

        config()->set('rapidez.stock_id', $stockId);
        return (int)$stockId;
    }
}