<?php

namespace App\Traits;

trait ModelRelation
{
    /**
     * Get all relations
     * @return array
     */
    public function relations()
    {
        return [
            'Option'  => 'App\Models\Option',
            'Meeting' => 'App\Models\Meeting',
        ];
    }
}
