<?php

namespace App\Models\Config;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = ['name', 'value'];

    const ASSET_TYPES = [
        'logo',
        'logo_light',
        'icon',
        'favicon',
        "icon_512",
        "icon_192",
        "icon_180",
        "icon_32",
        "icon_16"
    ];

    protected $casts = [
        'value' => 'json',
        'meta'  => 'json'
    ];

    public function getValue(string $option)
    {
        return Arr::get($this->value, $option);
    }
}
