<?php

namespace App\Http\Resources;

use App\Helpers\CalHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class Segment extends JsonResource
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
           'uuid'        => $this->uuid,
           'name'        => $this->name,
           'description' => $this->description,
           'meta'        => $this->meta,
           'created_at'  => CalHelper::toDateTime($this->created_at),
           'updated_at'  => CalHelper::toDateTime($this->updated_at)
        ];
    }
}
