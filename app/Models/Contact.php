<?php

namespace App\Models;

use App\Traits\HasMeta;
use App\Traits\HasUuid;
use App\Models\Segment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class Contact extends Model
{
    use HasMeta, HasUuid, LogsActivity;

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array'
    ];
    protected $table = 'contacts';
    protected static $logName = 'contact';
    protected static $logFillable = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = [ 'updated_at'];
    protected $with = ['segments', 'user', 'users'];
    protected $appends = ['contact_name'];

    // Relations
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'contact_user', 'contact_id', 'user_id')->withPivot('name');
    }

    public function segments()
    {
        return $this->belongsToMany(Segment::class, 'contact_segment', 'contact_id', 'segment_id');
    }

    public function getContactNameAttribute()
    {
        $contact_user = optional($this->users()->firstWhere('user_id', \Auth::id()))->pivot;

        return optional($contact_user)->name ?? $this->name;
    }

    // Booted
    public static function booted()
    {
    }

    protected static function ensureUpdatable() : void
    {
    }

    // Filters

    public function scopeFilterByKeyword(Builder $query, $keyword = null) : void
    {
        $query->when($keyword, function ($q, $keyword) {
            return $q->where(function ($q1) use ($keyword) {
                $q1->where('name', 'like', '%'.$keyword.'%')->orWhere('email', 'like', '%'.$keyword.'%');
            });
        });
    }

    public function scopeFilterByName(Builder $query, $name = null) : void
    {
        $query->when($name, function ($q, $name) {
            return $q->where('name', 'like', '%'.$name.'%');
        });
    }

    public function scopeFilterByEmail(Builder $query, $email = null) : void
    {
        $query->when($email, function ($q, $email) {
            return $q->where('email', 'like', '%'.$email.'%');
        });
    }

    public function scopeFilterByUserId(Builder $query, $user_id = null) : void
    {
        $query->when($user_id, function ($q, $user_id) {
            return $q->whereUserId($user_id);
        });
    }

    public function scopeVisibility(Builder $query) : void
    {
        $query->whereHas('users', function($q1) {
            $q1->whereUserId(\Auth::id());
        });
    }
}
