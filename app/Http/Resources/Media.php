<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Media extends JsonResource
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
            'uuid'                => $this->uuid,
            'name'                => $this->name,
            'position'            => $this->order_column,
            'link'                => $this->getCustomProperty('link'),
            'link_type'           => $this->getCustomProperty('link_type'),
            'new_tab'             => $this->getCustomProperty('new_tab', false),
            'filename'            => $this->file_name,
            'mime'                => $this->mime_type,
            'size'                => $this->size,
            'human_readable_size' => $this->human_readable_size,
            'url'                 => $this->getUrl(),
            'full_url'            => $this->getFullUrl(),
        ];
    }
}
