<?php

namespace Rapidez\Msi\Models\Scopes\Product;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WithStockQtyScope extends WithProductStockScopeMsi
{
    public function apply(Builder $builder, Model $model)
    {
        $stockId = config('rapidez.stock_id', $this->getInventoryStockId());
        $table = 'inventory_stock_' . $stockId;

        $query = DB::table($table)
            ->selectRaw('ANY_VALUE('.$table.'.quantity) + COALESCE(SUM(inventory_reservation.quantity),0)')
            ->leftJoin('inventory_reservation', $table.'.sku', '=', 'inventory_reservation.sku')
            ->whereColumn($table.'.sku', $model->getTable() . '.sku')
            ->groupBy('inventory_reservation.sku');

        $builder->selectSub($query, 'qty');
    }
}
