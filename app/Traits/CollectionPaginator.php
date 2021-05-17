<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

trait CollectionPaginator
{
    /**
     * Paginate given collection
     * @param  collection  $items
     * @param  integer  $per_page
     * @param  integer  $page
     * @param  array  $options
     * @return array
     */
    public function collectionPaginate($items, $per_page = 5, $page = null, $options = [])
    {
        $page            = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items           = $items instanceof Collection ? $items : Collection::make($items);
        $paginated_items =  new LengthAwarePaginator($items->forPage($page, $per_page), $items->count(), $per_page, $page, $options);
        $items           = $paginated_items->setPath(request()->url());
        $items           = $items->toArray();
        $data['data']   = array_values($items['data']);
        $data['links'] = array(
            'first' => $items['first_page_url'],
            'last'  => $items['last_page_url'],
            'prev'  => $items['prev_page_url'],
            'next'  => $items['next_page_url'],
        );
        $data['meta'] = array(
            'current_page' => $items['current_page'],
            'from'         => $items['from'],
            'last_page'    => $items['last_page'],
            'path'         => $items['path'],
            'per_page'     => (int) $items['per_page'],
            'to'           => $items['to'],
            'total'        => $items['total'],
        );
        return $data;
    }
}
