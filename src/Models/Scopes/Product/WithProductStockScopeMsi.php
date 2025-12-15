<?php

namespace Rapidez\Msi\Models\Scopes\Product;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WithProductStockScopeMsi implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $stockId = config('rapidez.stock_id', $this->getInventoryStockId());
        $table = "inventory_stock_$stockId";

        /**
         * Overwrites the already existing eager load with a different query.
         *
         * By doing this we keep the `ProductStock` class intact, but we inject some data from the `inventory_stock_` tables.
         * It's a bit ugly but it gets the job done very cleanly
         * (as long as you don't, for whatever reason, use `->stock()->get()` instead of `->stock`)
         */
        $builder->with('stock', function (BelongsTo $relation) use ($table, $model) {
            // Get product ID from the `where` binding
            $productId = $relation->getBaseQuery()->bindings['where'][0];

            // Then remove the bindings...
            $relation->getBaseQuery()->wheres = [];
            $relation->getBaseQuery()->bindings['where'] = [];

            // ...and rebuild the query from scratch
            $relation
                ->from($table)
                ->select([
                    "$table.product_id",
                    "$table.stock_id",
                    "$table.quantity as qty",
                    "$table.is_salable as is_in_stock",
                    'backorders', 'use_config_backorders',
                    'min_sale_qty', 'use_config_min_sale_qty',
                    'max_sale_qty', 'use_config_max_sale_qty',
                    'qty_increments', 'use_config_qty_increments',
                ])
                ->join('cataloginventory_stock_item', function(JoinClause $join) use ($table) {
                    $join->on('cataloginventory_stock_item.product_id', '=', "$table.product_id")
                        ->whereIn("$table.website_id", [0, config('rapidez.website')]);
                })
                ->where("$table.product_id", $productId)
                ->whereIn("$table.website_id", [0, config('rapidez.website')]);
        });
    }

    /**
     * Used primarily as fallback when global scopes are used
     * on the Rapidez indexer as stock_id variable set
     * in HTTP Middleware is not available.
     *
     * TODO: This should be moved to a better place! Maybe directly
     * in the core where the current store is determined?
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
