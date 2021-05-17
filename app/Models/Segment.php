<?php

namespace App\Models;

use App\Traits\HasMeta;
use App\Traits\HasUuid;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;

class Segment extends Model
{
    use HasMeta, HasUuid, LogsActivity;

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array'
    ];
    protected $table = 'segments';
    protected static $logName = 'segment';
    protected static $logFillable = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = [ 'updated_at'];

    // Relations
    public function contacts() : BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_segment', 'segment_id', 'contact_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'segment_user', 'segment_id', 'user_id');
    }

    // Booted
    public static function booted()
    {
    }

    protected static function ensureUpdatable() : void
    {
    }

    // Filters

    public function scopeFilterByName(Builder $query, $name = null) : void
    {
        $query->when($name, function ($q, $name) {
            return $q->where('name', 'like', '%'.$name.'%');
        });
    }

    public function scopeVisibility(Builder $query) : void
    {
        $query->whereHas('users', function($q1) {
            $q1->whereUserId(\Auth::id());
        });
    }
}
