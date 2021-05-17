<?php

namespace App\Models;

use App\Models\User;
use App\Traits\HasMeta;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Chat extends Model implements HasMedia
{
    use HasMeta, HasUuid, InteractsWithMedia, LogsActivity;

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array'
    ];
    protected $table = 'chats';
    protected static $logName = 'chat';
    protected static $logFillable = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = [ 'updated_at'];

    // Relations
    public function chatRoom() : BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Booted
    public static function booted()
    {
    }

    protected static function ensureUpdatable() : void
    {
    }

    // Filters

    public function scopeFilterByBody(Builder $query, $keyword = null) : void
    {
        $query->when($keyword, function ($q, $keyword) {
            return $q->where('body', 'like', '%'.$keyword.'%');
        });
    }

    public function scopeFilterByRoomId(Builder $query, $chat_room_id = null) : void
    {
        $query->when($chat_room_id, function ($q, $chat_room_id) {
            return $q->whereChatRoomId($chat_room_id);
        });
    }

    public function scopeFilterByUserId(Builder $query, $user_id = null) : void
    {
        $query->when($user_id, function ($q, $user_id) {
            return $q->whereUserId($user_id);
        });
    }
}
