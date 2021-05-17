<?php

namespace App\Models\Site;

use App\Traits\HasMeta;
use App\Traits\HasUuid;
use App\Traits\ModelOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Query extends Model
{
    use ModelOption, HasUuid, HasMeta, LogsActivity;

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array'
    ];
    protected $table = 'site_queries';
    protected static $logName = 'query';
    protected static $logFillable = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = [ 'updated_at'];
    protected static $sortOptions = ['name', 'email', 'created_at'];
    protected static $defaultSortBy = 'created_at';

    // Relations

    // Booted
    public static function booted()
    {
    }

    protected static function ensureUpdatable() : void
    {
    }

    // Filters

    public function scopeFilterByEmail(Builder $query, $email = null) : void
    {
        $query->when($email, function ($q, $email) {
            return $q->where('email', 'like', '%'.$email.'%');
        });
    }

    public function scopeFilterByName(Builder $query, $name = null) : void
    {
        $query->when($name, function ($q, $name) {
            return $q->where('name', 'like', '%'.$name.'%');
        });
    }
}