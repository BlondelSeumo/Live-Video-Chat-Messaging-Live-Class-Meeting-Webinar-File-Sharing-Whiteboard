<?php

namespace App\Http\Resources;

use App\Helpers\ArrHelper;
use App\Helpers\CalHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUser extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $gender = ArrHelper::searchByKey(ArrHelper::getTransList('genders'), 'uuid', $this->gender);

        return [
            'uuid'        => $this->uuid,
            'username'    => $this->username,
            'email'       => $this->email,
            'roles'       => $this->roles()->pluck('name')->all(),
            'permissions' => $this->getAllPermissions()->pluck('name')->all(),
            'profile'     => array(
                'name'       => $this->name,
                'avatar'     => $this->avatar,
                'gender'     => $gender,
                'birth_date' => CalHelper::toDate($this->birth_date),
                'age'        => CalHelper::getAge($this->birth_date)
            ),
            'preference' => $this->user_preference
        ];
    }
}