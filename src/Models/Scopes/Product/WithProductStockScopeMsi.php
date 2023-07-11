<?php

namespace Rapidez\Msi\Models\Scopes\Product;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithProductStockScopeMsi implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Remove the existing "in_stock" select.
        $builder->getQuery()->columns = collect($builder->getQuery()->columns)->filter(function ($column) {
            return !Str::endsWith((string)$column, 'in_stock');
        })->toArray();

        // Remove the "cataloginventory_stock_item AS children_stock" join.
        foreach ($builder->getQuery()->joins as $key => $join) {
            if (str_contains($join->table, 'children_stock')) {
                unset($builder->getQuery()->joins[$key]);
            }
        }

        $stockId = config('rapidez.stock_id', $this->getInventoryStockId());

        $builder
            ->selectRaw('ANY_VALUE(inventory_stock_' . $stockId . '.is_salable) AS in_stock')
            ->leftJoin('inventory_stock_' . $stockId, $model->getTable() . '.sku', '=', 'inventory_stock_' . $stockId . '.sku');
    }

    /**
     * Used primarily as fallback when global scopes are used
     * on the Rapidez indexer as stock_id variable set
     * in HTTP Middleware is not available.
     *
     * @return int
     */
    public function getInventoryStockId(): int
    {
        $stockId = Cache::rememberForever('stock_id_store_'.config('rapidez.store'), function () {
            return DB::table('inventory_stock_sales_channel')
                ->join('store_website', 'store_website.code', '=', 'inventory_stock_sales_channel.code')
                ->join('store', 'store.website_id', '=', 'store_website.website_id')
                ->where('inventory_stock_sales_channel.type', 'website')
                ->where('store.store_id', config('rapidez.store'))
                ->first('stock_id')
                ->stock_id;
        });

        config()->set('rapidez.stock_id', $stockId);

        return (int)$stockId;
    }
}
