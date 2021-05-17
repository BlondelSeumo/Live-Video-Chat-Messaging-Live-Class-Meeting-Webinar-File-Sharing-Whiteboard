<?php

namespace App\Http\Resources\Site;

use App\Helpers\CalHelper;
use App\Http\Resources\Media as MediaResource;
use App\Http\Resources\Option as OptionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Page extends JsonResource
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
           'uuid'       => $this->uuid,
           'title'      => $this->title,
           'body'       => $this->body,
           'slug'       => $this->slug,
           'status'     => $this->status ? true : false,
           'meta'       => $this->meta,
           'template'   => $this->template ? new OptionResource($this->whenLoaded('template')) : null,
           'parent'     => $this->parent ? new Page($this->whenLoaded('parent')) : null,
           'media'      => count($media) ? new MediaResource($media[0]) : null,
           'created_at' => $this->created_at->format(CalHelper::getSysDateTimeFormat()),
           'updated_at' => $this->updated_at->format(CalHelper::getSysDateTimeFormat())
        ];
    }
}
