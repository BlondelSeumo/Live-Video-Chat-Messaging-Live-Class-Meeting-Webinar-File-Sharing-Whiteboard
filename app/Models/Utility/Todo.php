<?php

namespace App\Models\Utility;

use App\Models\User;
use App\Helpers\CalHelper;
use App\Traits\ModelOption;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Todo extends Model
{
    use ModelOption, LogsActivity;

    protected $guarded = [];
    protected $casts = [
        'meta'         => 'array',
        'due_date'     => 'date',
        'due_time'     => 'time',
        'completed_at' => 'datetime'
    ];
    protected $table = 'todos';
    protected static $logName = 'todo';
    protected static $logFillable = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = [ 'updated_at'];
    protected static $sortOptions = ['created_at', 'due_date', 'title'];
    protected static $defaultSortBy = 'due_date';

    // Relations
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Meta
    public function getMeta(string $option)
    {
        return Arr::get($this->meta, $option);
    }

    // Booted
    public static function booted()
    {
        static::creating(function (Todo $todo) {
            if ($todo->status === null) {
                $todo->status = 0;
            }
        });
    }

    // Concerns
    public static function isPending() : bool
    {
        return ! $this->completed_at ? true : false;
    }

    protected static function ensureUpdatable() : void
    {
        // if ($this->completed_at) {
        //  throw CouldNotUpdate::isCompleted($this);
        // }
    }

    // Filters
    public function scopeFilterById(Builder $query, $id) : void
    {
        $query->when($id, function ($q, $id) {
            return $q->where('id', '=', $id);
        });
    }

    public function scopeFilterByUuid(Builder $query, $uuid) : void
    {
        $query->when($uuid, function ($q, $uuid) {
            return $q->where('uuid', '=', $uuid);
        });
    }

    public function scopeFilterByKeyword(Builder $query, $keyword = null) : void
    {
        $query->when($keyword, function ($q, $keyword) {
            return $q->where(function ($q1) {
                $q1->where('title', 'like', '%'.$keyword.'%')->orWhere('description', 'like', '%'.$keyword.'%');
            });
        });
    }

    public function scopeFilterCompleted(Builder $query, $status = null) : void
    {
        $query->when($status, function ($q, $status) {
            return $q->whereStatus(1);
        });
    }

    public function scopeFilterByDueDate(Builder $query, $due_date = null) : void
    {
        $query->when($due_date, function ($q, $due_date) {
            $due_date = $due_date ? CalHelper::toDate($due_date) : CalHelper::today();
            return $q->whereDueDate($due_date);
        });
    }

    public function scopeFilterByDueTime(Builder $query, $due_time = null) : void
    {
        $query->when($due_time, function ($q, $due_time) {
            $due_time = $due_time ? CalHelper::toTime($due_time) : CalHelper::today();
            return $q->whereDueTime($due_time);
        });
    }

    public function scopeFilterByUserId(Builder $query, $user_id = null) : void
    {
        $query->when($user_id, function ($q, $user_id) {
            return $q->whereUserId($user_id);
        });
    }

    public function scopeDateBetween(Builder $query, $dates) : void
    {
        $start_date = Arr::get($dates, 'start_date');
        $end_date = Arr::get($dates, 'end_date') ? : $start_date;

        if ($start_date && $end_date && $start_date <= $end_date) {
            $query->where('due_date', '>=', $start_date)->where('due_date', '<=', $end_date);
        }
    }
}
