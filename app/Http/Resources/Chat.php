<?php

namespace App\Http\Resources;

use App\Helpers\CalHelper;
use App\Http\Resources\ChatRoomSummary;
use App\Http\Resources\UserSummary;
use Illuminate\Http\Resources\Json\JsonResource;

class Chat extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $media = $this->getMedia();

        return [
            'uuid'        => $this->uuid,
            'chat_room'   => new ChatRoomSummary($this->whenLoaded('chatRoom')),
            'user'        => new UserSummary($this->whenLoaded('user')),
            'attachments' => new MediaCollection($media),
            'message'     => $this->message ? : ($media ? trans('upload.attachment') : null),
            'sent_at'     => CalHelper::toDateTime($this->created_at),
            'status'      => 'sent',
        ];
    }
}
