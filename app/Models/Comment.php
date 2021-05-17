<?php

namespace App\Models;

use App\Traits\HasMeta;
use App\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Comment extends Model implements HasMedia
{
    use HasMeta, HasUuid, InteractsWithMedia, LogsActivity;

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array'
    ];
    protected $table = 'comments';
    protected static $logName = 'comment';
    protected static $logFillable = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = [ 'updated_at'];

    // Relations
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    // Booted
    public static function booted()
    {
    }

    protected static function ensureUpdatable() : void
    {
    }

    // Constraints

    public function isValid($meeting) : void
    {
        $commentable = $this->commentable;

        if (! $commentable instanceof Meeting) {
            throw ValidationException::withMessages(['message' => __('general.invalid_action')]);
        }

        if ($commentable->id != $meeting->id) {
            throw ValidationException::withMessages(['message' => __('general.invalid_action')]);
        }
    }

    // Filters
}