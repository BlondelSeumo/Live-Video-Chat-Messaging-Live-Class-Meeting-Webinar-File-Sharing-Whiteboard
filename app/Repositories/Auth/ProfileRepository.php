<?php

namespace App\Repositories\Auth;

use App\Helpers\ArrHelper;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ProfileRepository
{
    /**
     * Update profile
     */
    public function update() : void
    {
        $user = \Auth::user();

        $gender = Arr::get(request('gender', []), 'uuid');

        if (! in_array($gender, ArrHelper::getList('genders'))) {
            throw ValidationException::withMessages(['message' => trans('global.could_not_find', ['attribute' => trans('user.props.gender')])]);
        }

        $user->name = request('name');
        $user->birth_date = request('birth_date');
        $user->gender = $gender;
        $user->save();
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar() : User
    {
        request()->validate([
            'file' => 'required|image'
        ]);

        $user = \Auth::user();

        if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
            \Storage::disk('public')->delete($user->avatar);
        }

        $file = \Storage::disk('public')->putFile('avatar', request()->file('file'));
        $user->avatar = '/storage/'.$file;
        $user->save();

        return $user;
    }

    /**
     * Remomve avatar
     */
    public function removeAvatar() : void
    {
        $user = \Auth::user();

        if (! $user->avatar) {
            return;
        }

        if (\Storage::disk('public')->exists($user->avatar)) {
            \Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = null;
        $user->save();
    }
}
