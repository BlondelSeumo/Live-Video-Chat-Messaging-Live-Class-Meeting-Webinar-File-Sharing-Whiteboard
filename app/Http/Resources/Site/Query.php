<?php

namespace App\Http\Resources\Site;

use App\Helpers\CalHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class Query extends JsonResource
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
           'uuid'           => $this->uuid,
           'name'           => $this->name,
           'email'          => $this->email,
           'contact_number' => $this->contact_number,
           'subject'        => $this->subject,
           'message'        => $this->message,
           'created_at'     => CalHelper::toDateTime($this->created_at),
           'updated_at'     => CalHelper::toDateTime($this->updated_at)
        ];
    }
}