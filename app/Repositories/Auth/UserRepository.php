<?php

namespace App\Repositories\Auth;

use App\Enums\Auth\UserStatus;
use App\Helpers\ArrHelper;
use Illuminate\Support\Str;
use App\Http\Resources\UserCollection;
use App\Models\Contact;
use App\Models\Config\Role;
use App\Models\User;
use App\Repositories\Config\RoleRepository;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UserRepository
{
    protected $user;
    protected $contact;
    protected $role;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        User $user,
        Contact $contact,
        RoleRepository $role
    ) {
        $this->user = $user;
        $this->contact = $contact;
        $this->role = $role;
    }

    /**
     * Find user with given id or throw an error
     * @param integer $id
     */
    public function findOrFail($id, $field = 'message') : User
    {
        $user = $this->user->find($id);

        if (! $user) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('user.user')])]);
        }

        return $user;
    }

    /**
     * Find user with given uuid or throw an error
     * @param string $uuid
     */
    public function findByUuidOrFail($uuid, $field = 'message') : User
    {
        $user = $this->user->filterByUuid($uuid)->first();

        if (! $user) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('user.user')])]);
        }

        return $user;
    }

    /**
     * Find user by email
     *
     * @param email $email
     */
    public function findByEmail($email) : ?User
    {
        return $this->user->whereEmail($email)->first();
    }

    /**
     * Paginate all users
     */
    public function paginate() : UserCollection
    {
        $sort_by    = request()->query('sort_by', 'created_at');
        $order      = request()->query('order', 'desc');
        $name       = request()->query('name');
        $username   = request()->query('username');
        $email      = request()->query('email');
        $start_date = request()->query('start_date');
        $end_date   = request()->query('end_$end_date');

        $query = $this->user->filterByName($name)->filterByUsername($username)->filterByEmail($email)->dateBetween([
                'start_date' => $start_date,
                'end_date'   => $end_date
            ]);

        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return new UserCollection($query->orderBy($sort_by, $order)->paginate((int) $per_page, ['*'], 'current_page'));
    }

    /**
     * Get user pre requisite
     */
    public function getPreRequisite() : array
    {
        $genders = ArrHelper::getTransList('genders');
        $roles = $this->role->getAll([
            'exclude_admin' => true
        ]);

        return compact('genders', 'roles');
    }

    /**
     * Create a new user
     */
    public function create() : User
    {
        $user = $this->user->forceCreate($this->formatParams());

        $this->updateContact($user);

        $role = Role::whereName(Arr::get(request('role'), 'uuid'))->first();

        $user->assignRole($role->name);

        return $user;
    }

    /**
     * Prepare given params for inserting into database
     * @param uuid $uuid
     */
    private function formatParams($uuid = null) : array
    {
        $gender = Arr::get(request('profile.gender', []), 'uuid');

        if (! in_array($gender, ArrHelper::getList('genders'))) {
            throw ValidationException::withMessages(['profile.gender' => trans('global.could_not_find', ['attribute' => trans('user.props.gender')])]);
        }

        $role = Arr::get(request('role'), 'uuid');

        $roles = $this->role->getAll([
            'exclude_admin' => true
        ])->pluck('name')->all();

        if (! in_array($role, $roles)) {
            throw ValidationException::withMessages(['role' => trans('global.could_not_find', ['attribute' => trans('config.role.role')])]);
        }

        $formatted = [
            'name'       => request('profile.name'),
            'birth_date' => request('profile.birth_date') ? : null,
            'gender'     => $gender,
            'username'   => request('username'),
            'email'      => request('email'),
        ];

        if (! $uuid) {
            $formatted['password'] = bcrypt(request('password'));
            $formatted['uuid']     = Str::uuid();
            $formatted['status']   = UserStatus::ACTIVATED;
        }

        return $formatted;
    }

    /**
     * Update given user
     * @param User $user
     */
    public function update(User $user) : User
    {
        if (request()->boolean('force_password')) {
            $user->password = bcrypt(request('password'));
        }

        $user->forceFill($this->formatParams($user->uuid))->save();

        $user->save();

        $this->updateContact($user);

        $role = Role::whereName(Arr::get(request('role'), 'uuid'))->first();

        if (! $user->hasRole($role->name)) {
            $user->removeRole($user->getRoleNames()->first());
            $user->assignRole($role->name);
        }
        
        return $user;
    }

    /**
     * Update auth user subscription
     */
    public function updateSubscription() : void
    {
        request()->validate(['endpoint' => 'required']);

        request()->user()->updatePushSubscription(
            request('endpoint'),
            request('public_key'),
            request('auth_token'),
            request('content_encoding')
        );
    }

    /**
     * Delete auth user subscription
     */
    public function deleteSubscription() : void
    {
        request()->validate(['endpoint' => 'required']);

        request()->user()->deletePushSubscription(request('endpoint'));
    }

    /**
     * Update given user status
     * @param User $user
     */
    public function updateStatus(User $user) : User
    {
        if ($user->status === UserStatus::PENDING_APPROVAL && ! in_array(request('status'), [UserStatus::ACTIVATED, UserStatus::DISAPPROVED])) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }

        if ($user->status === UserStatus::ACTIVATED && ! in_array(request('status'), [UserStatus::BANNED])) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }

        if ($user->status === UserStatus::BANNED && ! in_array(request('status'), [UserStatus::ACTIVATED])) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }

        $user->status = request('status');
        $user->save();

        return $user;
    }

    /**
     * Update contact user
     *
     * @param User $user
     */
    private function updateContact(User $user) : void
    {
        $contact = $this->contact->whereEmail($user->email)->first();

        if ($contact && $contact->user_id != $user->id) {
            $contact->user_id = $user->id;
            $contact->save();
        }

        if (! $contact) {
            $contact = $this->contact->forceCreate([
                'user_id' => $user->id,
                'email' => request('email'),
            ]);

            $contact->users()->syncWithoutDetaching([\Auth::id() => ['name' => request('profile.name')]]);
        }
    }

    /**
     * Delete user
     * @param User $user
     */
    public function delete(User $user) : void
    {
        if ($user->hasRole('admin')) {
            throw ValidationException::withMessages(['message' => trans('user.permission_denied')]);
        }

        $user->delete();
    }

    /**
     * Store user preference
     */
    public function preference() : void
    {
        $user = \Auth::user();

        $data = request()->validate([
            'system.date_format' => 'sometimes',
            'system.time_format' => 'sometimes',
            'system.locale' => 'sometimes',
            'system.timezone' => 'sometimes',
            'chat.enable_auto_open' => 'sometimes|boolean',
            'chat.enter_to_submit' => 'sometimes|boolean',
            'chat.mute_sound_notification' => 'sometimes|boolean'
        ]);

        $user->preference = array_merge((is_array($user->preference) ? $user->preference : []), $data);
        $user->save();
        
        cache()->forget('query_list_all_config');
    }
}
