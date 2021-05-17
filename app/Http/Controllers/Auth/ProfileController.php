<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ProfileRequest;
use App\Repositories\Auth\ProfileRepository;

class ProfileController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        ProfileRepository
        $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Update profile
     * @get ("/api/profile")
     * @return array
     */
    public function update(ProfileRequest $request)
    {
        $this->repo->update();

        return $this->success(['message' => trans('global.updated', ['attribute' => trans('user.profile')])]);
    }

    /**
     * Upload avatar
     * @post ("/api/profile/avatar")
     * @param ({
     *      @Parameter("file", type="file", required="true", description="Avatar to be uploaded"),
     * })
     * @return array
     */
    public function uploadAvatar()
    {
        $user = $this->repo->uploadAvatar();

        return $this->success(['message' => __('global.uploaded', ['attribute' => __('user.props.avatar')]), 'avatar' => $user->avatar]);
    }

    /**
     * Remove avatar
     * @post ("/api/profile/avatar")
     * @return Response
     */
    public function removeAvatar()
    {
        $this->repo->removeAvatar();

        return $this->success(['message' => __('global.removed', ['attribute' => __('user.props.avatar')])]);
    }
}
