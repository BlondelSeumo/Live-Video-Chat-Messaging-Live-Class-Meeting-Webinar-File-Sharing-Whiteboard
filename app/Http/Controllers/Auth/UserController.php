<?php

namespace App\Http\Controllers\Auth;

use App\Http\Resources\AuthUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserRequest;
use App\Models\User;
use App\Repositories\Auth\UserRepository;
use App\Http\Resources\User as UserResource;

class UserController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        UserRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Authenticated user
     * @get ("/api/auth/user")
     * @return array
     */
    public function me()
    {
        return new AuthUser(\Auth::user());
    }

    /**
     * Store user preference
     * @post ("/api/user/preference")
     * @return array
     */
    public function preference()
    {
        $this->repo->preference();
        
        return $this->success(['message' => __('global.updated', ['attribute' => __('user.user_preference')])]);
    }

    /**
     * Get user pre requisite
     * @get ("/api/users/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Get all users
     * @get ("/api/users")
     * @return array
     */
    public function index()
    {
        $this->authorize('view', User::class);

        return $this->repo->paginate();
    }

    /**
     * Create user
     * @post ("/api/users")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="User name"),
     *      @Parameter("email", type="email", required="true", description="User email"),
     *      @Parameter("username", type="string", required="true", description="User username"),
     *      @Parameter("password", type="string", required="true", description="User password"),
     *      @Parameter("confirm_password", type="string", required="optional", description="User confirm password"),
     * })
     * @return array
     */
    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);

        $user = $this->repo->create();

        $user = new UserResource($user);

        return $this->success(['message' => __('global.added', ['attribute' => __('user.user')]), 'user' => $user]);
    }

    /**
     * Get user detail
     * @get ("/api/users/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="User unique id"),
     * })
     * @return UserResource
     */
    public function show(User $user)
    {
        $this->authorize('show', $user);

        $user->load('roles');

        return new UserResource($user);
    }

    /**
     * Update user
     * @patch ("/api/users/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="User unique id"),
     *      @Parameter("name", type="string", required="true", description="User name"),
     *      @Parameter("email", type="email", required="true", description="User email"),
     *      @Parameter("username", type="string", required="true", description="User username"),
     *      @Parameter("password", type="string", required="true", description="User password"),
     *      @Parameter("confirm_password", type="string", required="optional", description="User confirm password"),
     * })
     * @return array
     */
    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $user = $this->repo->update($user);

        return $this->success(['message' => __('global.updated', ['attribute' => __('user.user')])]);
    }

    /**
     * Update user subscription
     * @post ("/api/users/subscriptions")
     * @param ({
     * })
     * @return array
     */
    public function updateSubscription()
    {
        $user = $this->repo->updateSubscription();

        return $this->success([]);
    }

    /**
     * Delete user subscription
     * @post ("/api/users/subscriptions/delete")
     * @param ({
     * })
     * @return array
     */
    public function deleteSubscription()
    {
        $user = $this->repo->deleteSubscription();

        return $this->success([]);
    }

    /**
     * Update user status
     * @post ("/api/users/{uuid}/status")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="User unique id"),
     *      @Parameter("status", type="string", required="true", description="User status"),
     * })
     * @return array
     */
    public function updateStatus(User $user)
    {
        $this->authorize('update', $user);

        $user = $this->repo->updateStatus($user);

        return $this->success(['message' => __('global.updated', ['attribute' => __('user.user')])]);
    }

    /**
     * Delete user
     * @delete ("/api/users/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="User unique id"),
     * })
     * @return array
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $this->repo->delete($user);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('user.user')])]);
    }
}
