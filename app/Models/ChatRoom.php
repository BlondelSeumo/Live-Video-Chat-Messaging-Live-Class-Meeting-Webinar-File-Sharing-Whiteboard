<?php

namespace App\Models;

use App\Models\Chat;
use App\Traits\HasMeta;
use App\Traits\HasUuid;
use App\Models\ChatRoomMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;

class ChatRoom extends Model
{
    use HasMeta, HasUuid, LogsActivity;

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array'
    ];
    protected $table = 'chat_rooms';
    protected static $logName = 'chat';
    protected static $logFillable = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = [ 'updated_at'];

    // Relations
    public function chatRoomMembers() : HasMany
    {
        return $this->hasMany(ChatRoomMember::class);
    }

    public function chats() : HasMany
    {
        return $this->hasMany(Chat::class, 'chat_room_id');
    }

    // Booted
    public static function booted()
    {
    }

    protected static function ensureUpdatable() : void
    {
    }

    // Filters

    public function scopeWithLastMessage($query)
    {
        $query->addSelect(['message' => Chat::select('message')
            ->whereColumn('chat_room_id', 'chat_rooms.id')
            ->latest()
            ->take(1)
        ]);
    }
}
