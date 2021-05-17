<?php

namespace App\Models\Site;

use App\Models\Option;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Page extends Model implements HasMedia
{
    use LogsActivity;
    use InteractsWithMedia;

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array',
    ];
    protected $table = 'site_pages';
    protected static $logName = 'page';
    protected static $logFillable = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = [ 'updated_at'];

    // Relations
    public function template() : BelongsTo
    {
        return $this->belongsTo(Option::class, 'template_id');
    }

    public function parent() : BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    // Meta
    public function getMeta(string $option)
    {
        return Arr::get($this->options, $option);
    }

    // Booted
    public static function booted()
    {
        static::creating(function (Page $page) {
            if ($page->status === null) {
                $page->status = 0;
            }
        });
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

    public function scopeFilterByStatus(Builder $query, $status = null) : void
    {
        $query->when($status, function ($q, $status) {
            $status = $status == true || $status == 'true' ? 1 : 0;
            return $q->where('status', '=', $status);
        });
    }

    public function scopeFilterBySlug(Builder $query, $slug = null) : void
    {
        $query->when($slug, function ($q, $slug) {
            return $q->where('slug', '=', $slug);
        });
    }

    public function scopeFilterByTitle(Builder $query, $title = null) : void
    {
        $query->when($title, function ($q, $title) {
            return $q->where('title', 'like', '%'.$title.'%');
        });
    }
}
