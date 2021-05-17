<?php

namespace App\Events;

use App\Models\Meeting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PublicMeetingRoomCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $meeting;
    protected $data;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Meeting $meeting, $data)
    {
        $this->meeting = $meeting;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('MeetingGuest.'.$this->meeting->uuid);
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
            'roomId' => $this->data['room_id']
        ];
    }
}
