<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait HasMeta
{
    public static function bootHasMeta()
    {
    }

    public function getMeta(string $option)
    {
        return Arr::get($this->meta, $option);
    }
}
