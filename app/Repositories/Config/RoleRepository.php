<?php
namespace App\Repositories\Config;

use App\Models\User;
use App\Helpers\ArrHelper;
use App\Models\Config\Role;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\Config\RoleCollection;
use Illuminate\Validation\ValidationException;

class RoleRepository
{
    protected $role;
    protected $user;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Role $role,
        User $user
    ) {
        $this->role = $role;
        $this->user = $user;
    }

    /**
     * Get all roles
     * @param array $options
     */
    public function getAll($options = array()) : RoleCollection
    {
        $exclude_admin = array_get($options, 'exclude_admin');

        $query = $this->role->when($exclude_admin, function($q, $exclude_admin) {
            return $q->whereNotIn('name', ['admin']);;
        });

        return new RoleCollection($query->orderBy('name', 'asc')->get());
    }

    /**
     * Find role with given id or throw an error
     * @param integer $id
     * @param string $field
     */
    public function findOrFail($id, $field = 'message') : Role
    {
        $role = $this->role->find($id);

        if (! $role) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('config.role.role')])]);
        }

        return $role;
    }

    /**
     * Get role by name
     * @param string $name
     */
    public function findByName($name = null) : Role
    {
        return $this->role->filterByName($name)->first();
    }

    /**
     * Get role by uuid
     * @param uuid $uuid
     */
    public function findByUuid($uuid = null) : Role
    {
        return $this->role->filterByUuid($uuid)->first();
    }

    /**
     * Find role with given uuid or throw an error
     * @param uuid $uuid
     */
    public function findByUuidOrFail($uuid, $field = 'message') : Role
    {
        $role = $this->role->filterByUuid($uuid)->first();

        if (! $role) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('config.role.role')])]);
        }

        return $role;
    }

    /**
     * Find role with given name or throw an error
     * @param string $name
     */
    public function findByNameOrFail($name, $field = 'message') : Role
    {
        $role = $this->role->whereName($name)->first();

        if (! $role) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('config.role.role')])]);
        }

        return $role;
    }

    /**
     * Get all filtered data
     */
    public function getData() : Builder
    {
        $sort_by = request('sort_by', 'name');
        $order   = request('order', 'asc');

        return $this->role->orderBy($sort_by, $order);
    }

    /**
     * Paginate all roles
     */
    public function paginate() : RoleCollection
    {
        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return new RoleCollection($this->getData()->paginate((int) $per_page, ['*'], 'current_page'));
    }

    /**
     * Delete role
     * @param uuid $uuid
     */
    public function delete($uuid) : void
    {
        $role = $this->findByNameOrFail($uuid);

        if (in_array(strtolower($role->name), config('default.roles'))) {
            throw ValidationException::withMessages(['message' => __('global.cannot_delete_default', ['attribute' => __('config.role.role')])]);
        }

        if ($this->user->role($role->name)->count()) {
            throw ValidationException::withMessages(['message' => __('global.associated_with_dependency', ['attribute' => __('config.role.role'), 'dependency' => __('user.user')])]);
        }

        activity('role')->on($role)->withProperties(['attributes' => ['id' => $role->id, 'name' => $role->name]])->log('deleted');
        
        $role->delete();
    }
}
