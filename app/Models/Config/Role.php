<?php

namespace App\Models\Config;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'roles';

    public function scopeFilterByUuid($q, $uuid = null)
    {
        if (! $uuid) {
            return $q;
        }

        return $q->where('uuid', '=', $uuid);
    }

    public function scopeFilterByName($q, $name = null)
    {
        if (! $name) {
            return $q;
        }

        return $q->where('name', 'like', '%' . $name . '%');
    }
}
