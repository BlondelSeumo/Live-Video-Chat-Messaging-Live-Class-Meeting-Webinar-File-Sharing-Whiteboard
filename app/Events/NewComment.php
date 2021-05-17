<?php

namespace App\Events;

use App\Helpers\CalHelper;
use App\Http\Resources\MediaCollection;
use App\Models\Comment;
use App\Models\Meeting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewComment implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $meeting;
    protected $comment;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Meeting $meeting, Comment $comment)
    {
        $this->meeting = $meeting;
        $this->comment = $comment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('Meeting.'.$this->meeting->uuid);
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
        return [
            'uuid'        => $this->comment->uuid,
            'body'        => $this->comment->body,
            'attachments' => new MediaCollection($this->comment->getMedia()),
            'meta'        => $this->comment->meta,
            'user'        => array(
                'uuid'        => $this->comment->user->uuid,
                'name'        => $this->comment->user->name,
                'username'    => $this->comment->user->username,
            ),
            'createdAt'   => CalHelper::toDateTime($this->comment->created_at),
            'updatedAt'   => CalHelper::toDateTime($this->comment->updated_at)
        ];
    }
}
