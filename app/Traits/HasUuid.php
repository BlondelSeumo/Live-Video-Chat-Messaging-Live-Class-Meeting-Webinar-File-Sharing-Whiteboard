<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUuid
{
    public static $fake_uuid = null;

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public static function bootHasUuid()
    {
        static::creating(function (Model $model) {
            $model->uuid = static::$fake_uuid ?? (string) Str::uuid();
        });
    }

    public static function filterByUuid(string $uuid = null) : ?Builder
    {
        return static::when($uuid, function ($q, $uuid) {
            return $q->where('uuid', '=', $uuid);
        });
    }
}
