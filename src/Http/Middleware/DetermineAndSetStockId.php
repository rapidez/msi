<?php

namespace Rapidez\Msi\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class DetermineAndSetStockId
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $stockId = DB::table('inventory_stock_sales_channel')
            ->where('inventory_stock_sales_channel.type', 'website')
            ->where('inventory_stock_sales_channel.code', '=', function ($query) {
                $query
                    ->selectRaw('sw.code')
                    ->from('store_website as sw')
                    ->where('sw.website_id', config('rapidez.website'))
                    ->limit(1);
            })
            ->pluck('stock_id')
            ->first();

        config()->set('rapidez.stock_id', $stockId);

        return $next($request);
    }
}