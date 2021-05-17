<?php

namespace App\Events;

use App\Helpers\CalHelper;
use App\Http\Resources\ChatRoom as ChatRoomResource;
use App\Http\Resources\MediaCollection;
use App\Http\Resources\UserSummary as UserSummaryResource;
use App\Models\Chat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSentToRoom implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $chat;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('ChatRoom.'.$this->chat->chatRoom->uuid);
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
        $this->chat->loadMissing('chatRoom', 'user');

        return [
            'uuid'    => $this->chat->uuid,
            'chat_room' => (new ChatRoomResource($this->chat->chatRoom))->resolve(),
            'user' => (new UserSummaryResource($this->chat->user))->resolve(),
            'attachments' => new MediaCollection($this->chat->getMedia()),
            'message' => $this->chat->message,
            'sent_at' => CalHelper::toDateTime($this->chat->created_at),
            'status'  => 'sent',
        ];
    }
}
