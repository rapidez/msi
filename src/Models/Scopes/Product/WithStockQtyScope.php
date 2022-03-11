<?php

namespace Rapidez\Msi\Models\Scopes\Product;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Rapidez\Msi\Models\Scopes\Product\WithProductStockScopeMsi;

class WithStockQtyScope extends WithProductStockScopeMsi
{
    public function apply(Builder $builder, Model $model)
    {
        $stockId = config()->has('rapidez.stock_id') ? config('rapidez.stock_id') : $this->getInventoryStockId();
        $query = DB::table('inventory_stock_' . $stockId, 'is')
            ->selectRaw('is.quantity + COALESCE(SUM(ir.quantity),0)')
            ->leftJoin('inventory_reservation AS ir', 'is.sku', '=', 'ir.sku')
            ->whereColumn('is.sku', $model->getTable() . '.sku')
            ->groupBy('ir.sku');

        $builder
            ->selectSub($query, 'stock_qty');
    }
}
