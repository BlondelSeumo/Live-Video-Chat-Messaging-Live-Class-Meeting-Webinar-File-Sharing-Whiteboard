<?php

namespace App\Http\Resources\Config;

use App\Helpers\ArrHelper;
use App\Helpers\CalHelper;
use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;

class Role extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $acl   = ArrHelper::getVar('acl');
        $roles = Arr::get($acl, 'roles', []);

        return [
            'uuid'       => $this->name,
            'name'       => title_case($this->name),
            'is_default' => array_search(strtolower($this->name), $roles) === false ? false : true,
            'created_at' => CalHelper::toDateTime($this->created_at),
            'updated_at' => CalHelper::toDateTime($this->updated_at)
        ];
    }
}
