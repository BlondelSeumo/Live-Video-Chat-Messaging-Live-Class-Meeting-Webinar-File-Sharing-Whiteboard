<?php

namespace App\Http\Resources;

use App\Helpers\CalHelper;
use App\Http\Resources\User;
use App\Http\Resources\SegmentCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class Contact extends JsonResource
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
           'uuid'       => $this->uuid,
           'name'       => $this->contact_name ?? $this->email,
           'email'      => $this->email,
           'meta'       => $this->meta,
           'segments'   => new SegmentCollection($this->whenLoaded('segments')),
           'user'       => new User($this->whenLoaded('user')),
           'created_at'  => CalHelper::toDateTime($this->created_at),
           'updated_at'  => CalHelper::toDateTime($this->updated_at)
        ];
    }
}
