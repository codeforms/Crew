<?php
namespace CodeForms\Repositories\Crew\Traits;

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
     * 
     * @return mixed
     */
    public function hasRole($roles)
    {
        if(is_string($roles))
            return $this->roles->contains('slug', $roles);

        if(is_array($roles))
            foreach($roles as $role)
                return $this->hasRole($role);
    }

    /**
     * @param $role
     */
    public function setRole($role)
    {
        return $this->roles()->sync($this->getRole($role));
    }

    /**
     * @param $role
     *
     * @return mixed
     */
    protected function getRole($role)
    { 
        if($role = Role::whereIn('slug', (array)$role)->first())
            return $role;

        return null;
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