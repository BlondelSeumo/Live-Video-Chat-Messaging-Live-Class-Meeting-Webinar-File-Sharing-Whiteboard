<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait ModelOption
{
    public static function bootModelOption()
    {
    }

    public function getSortBy() : string
    {
        $sortBy = request()->query('sort_by');

        $sortOptions = isset(static::$sortOptions) ? static::$sortOptions : ['created_at'];
        $defaultSortBy = static::$defaultSortBy ? static::$defaultSortBy : 'created_at';

        return in_array($sortBy, $sortOptions) ? $sortBy : $defaultSortBy;
    }

    public function getOrder($defaultOrderBy = 'desc') : string
    {
        $order =  request()->query('order', 'desc');

        return in_array($order, ['asc', 'dec']) ? $order : $defaultOrderBy;
    }
}
