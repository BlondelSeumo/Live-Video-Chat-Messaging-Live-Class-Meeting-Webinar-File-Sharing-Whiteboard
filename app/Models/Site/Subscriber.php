<?php

namespace App\Models\Site;

use App\Traits\HasMeta;
use App\Traits\ModelOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Subscriber extends Model
{
    use ModelOption, HasMeta, LogsActivity;

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array'
    ];
    protected $table = 'site_subscribers';
    protected static $logName = 'subscriber';
    protected static $logFillable = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = [ 'updated_at'];
    protected static $sortOptions = ['email', 'created_at'];
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
}