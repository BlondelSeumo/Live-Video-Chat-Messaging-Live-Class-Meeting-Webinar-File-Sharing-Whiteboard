<?php

namespace App\Events;

use App\Helpers\CalHelper;
use App\Http\Resources\ChatRoom as ChatRoomResource;
use App\Http\Resources\MediaCollection;
use App\Http\Resources\UserSummary as UserSummaryResource;
use App\Models\Chat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSentToUser implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $chat;
    protected $chat_room;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Chat $chat, $chat_room)
    {
        $this->chat = $chat;
        $this->chat_room = $chat_room;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = [];

        foreach ($this->chat_room->chatRoomMembers->where('left_at', null)->all() as $member) {
            $channels[] = new PrivateChannel('User.'.$member->user->uuid);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    // public function broadcastAs()
    // {
    //     return 'server.created';
    // }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $this->chat->loadMissing('user');
        $this->chat_room->loadMissing('chatRoomMembers');
        
        return [
            'uuid'     => $this->chat->uuid,
            'chat_room' => (new ChatRoomResource($this->chat_room))->resolve(),
            'user' => (new UserSummaryResource($this->chat->user))->resolve(),
            'attachments' => new MediaCollection($this->chat->getMedia()),
            'message'  => $this->chat->message,
            'sent_at' => CalHelper::toDateTime($this->chat->created_at),
            'status'   => 'sent',
        ];
    }
}
