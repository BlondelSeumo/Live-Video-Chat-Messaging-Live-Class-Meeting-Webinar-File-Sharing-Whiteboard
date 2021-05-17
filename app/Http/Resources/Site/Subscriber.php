<?php

namespace App\Http\Resources\Site;

use App\Helpers\CalHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class Subscriber extends JsonResource
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
           'uuid'            => $this->uuid,
           'email'           => $this->email,
           'unsubscribed_at' => CalHelper::toDateTime($this->unsubscribed_at),
           'created_at'      => CalHelper::toDateTime($this->created_at),
           'updated_at'      => CalHelper::toDateTime($this->updated_at)
        ];
    }
}