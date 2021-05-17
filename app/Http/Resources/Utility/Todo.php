<?php

namespace App\Http\Resources\Utility;

use App\Helpers\CalHelper;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Todo extends JsonResource
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
           'uuid'         => $this->uuid,
           'title'        => $this->title,
           'date'         => CalHelper::toDate($this->due_date),
           'time'         => $this->due_time ? CalHelper::toTime($this->due_time) : null,
           'status'       => $this->status ? true : false,
           'completed_at' => $this->completed_at ? $this->when($this->status && $this->completed_at, CalHelper::toDateTime($this->completed_at)) : null,
           'description'  => $this->description,
           'user'         => new UserResource($this->whenLoaded('user')),
           'created_at' => CalHelper::toDateTime($this->created_at),
           'updated_at' => CalHelper::toDateTime($this->updated_at)
        ];
    }
}
