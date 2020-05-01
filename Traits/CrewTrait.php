<?php
namespace CodeForms\Repositories\Crew\Traits;

use Illuminate\Database\Eloquent\Collection;
use CodeForms\Repositories\Crew\Models\Role;
use CodeForms\Repositories\Crew\Models\Permission;
use CodeForms\Repositories\Crew\Traits\PermissionTrait;
use CodeForms\Repositories\Crew\Exceptions\RoleDoesNotExist;
/**
 * 
 */
trait CrewTrait 
{
    /**
     * 
     */
    use PermissionTrait;

    /**
     * @param $roles
     * @example $user->hasRole('Manager')
     * @example $user->hasRole(['Manager', 'Editor']) (has any role)
     * 
     * @return boolean
     */
    public function hasRole($roles): bool
    {
        if(is_string($roles))
            return $this->roles->contains('slug', $roles);

        if(is_array($roles))
            foreach($roles as $role)
                return $this->hasRole($role);
    }

    /**
     * @param array|string|null $role
     * @example $user->setRole() (revoke all roles)
     * @example $user->setRole('Customer')
     * @example $user->setRole(['Manager', 'Customer'])
     * 
     * @return mixed
     */
    public function setRole($roles = null)
    {
        return $this->roles()->sync(self::getRoleObject($roles));
    }

    /**
     * @param string|array $role
     * @access private
     *
     * @return mixed
     */
    private function getRoleObject($roles)
    {
        if(is_string($roles))
            return self::findRole($roles);

        if(is_array($roles))
            return self::roleCollection($roles);
    }

    /**
     * @param string $slug
     * @access private
     * 
     * @return object|null
     */
    private function findRole($slug)
    {
        return Role::where('slug', $slug)->first();
    }

    /**
     * @param array $roles
     * @access private
     * 
     * @return object
     */
    private function roleCollection($roles): object
    {
        $collection = new Collection;

        foreach($roles as $role)
            $package = $collection->push(self::findRole($role));

        return $package->filter(function ($value) {
            return is_object($value);
        });
    }

    /**
     * 
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * 
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class,'user_permissions');
    }
}