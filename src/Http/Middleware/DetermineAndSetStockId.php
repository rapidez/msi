<?php

namespace Rapidez\Msi\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DetermineAndSetStockId
{
    public function handle($request, Closure $next)
    {
        $stockId = Cache::rememberForever('stock_id_website_'.config('rapidez.website'), function () {
            return DB::table('inventory_stock_sales_channel')
                ->where('inventory_stock_sales_channel.type', 'website')
                ->where('inventory_stock_sales_channel.code', '=', config('rapidez.website_code'))
                ->first('stock_id')
                ->stock_id;
        });

        config()->set('rapidez.stock_id', $stockId);

        return $next($request);
    }
}
