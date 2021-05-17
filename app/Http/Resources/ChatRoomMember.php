<?php

namespace App\Http\Resources;

use App\Helpers\CalHelper;
use App\Http\Resources\ChatRoom;
use App\Http\Resources\UserSummary;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomMember extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            // 'chat_room' => new ChatRoom($this->whenLoaded('chatRoom')),
            'user'         => new UserSummary($this->whenLoaded('user')),
            'username'     => optional($this->user)->username,
            'name'         => optional($this->user)->name,
            'joined_at'    => CalHelper::toDateTime($this->joined_at),
            'left_at'      => CalHelper::toDateTime($this->left_at),
            'is_owner'     => $this->is_owner ? true : false,
            'can_moderate' => ($this->is_owner || $this->is_moderator) ? true : false,
        ];
    }
}