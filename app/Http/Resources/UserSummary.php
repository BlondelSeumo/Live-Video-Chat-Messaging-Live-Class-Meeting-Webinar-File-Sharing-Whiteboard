<?php

namespace App\Http\Resources;

use App\Helpers\ArrHelper;
use App\Helpers\CalHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSummary extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $gender = ArrHelper::searchByKey(ArrHelper::getTransList('genders'), 'uuid', $this->gender ?? 'male');

        return [
           'uuid'         => $this->uuid,
           'username'     => $this->username,
           'email'        => $this->email,
           'profile'     => array(
               'name'       => $this->name,
               'avatar'     => $this->avatar,
               'gender'     => $gender,
               'birth_date' => CalHelper::toDate($this->birth_date),
               'age'        => CalHelper::getAge($this->birth_date)
           )
        ];
    }
}