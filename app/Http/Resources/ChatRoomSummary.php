<?php

namespace App\Http\Resources;

use App\Helpers\CalHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserSummary;
use App\Http\Resources\ChatCollection;
use App\Http\Resources\UserSummaryCollection;

class ChatRoomSummary extends JsonResource
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
           'uuid'                 => $this->uuid,
           'name'                 => $this->name,
           'is_group'             => $this->name != null,
           'last_conversation_at' => CalHelper::toDateTime($this->last_conversation_at),
           'created_at'           => CalHelper::toDateTime($this->created_at),
           'updated_at'           => CalHelper::toDateTime($this->updated_at)
        ];
    }
}
