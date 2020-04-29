<?php
namespace CodeForms\Repositories\Crew\Traits;

use Illuminate\Support\Arr;
use CodeForms\Repositories\Crew\Models\Permission;
/**
 * 
 */
trait PermissionTrait 
{
    /**
     * @param $permission
     * 
     * @return boolean
     */
    public function hasPermission($permission): bool
    {
        return self::hasRolePermission($permission) or self::hasUserPermission($permission);
    }

    /**
     * @param $permission
     * 
     * @return boolean
     */
    public function hasRolePermission($permission): bool
    {
        if((bool)$this->roles()->count())
            foreach($this->roles as $role)
                return $role->hasPermission($permission);

        return false;
    }
    
    /**
     * @param $permission
     * @access private
     * 
     * @return boolean
     */
    private function hasUserPermission($permission): bool
    {
        return (bool)$this->permissions->where('slug', $permission)->count();
    }

    /**
     * @param $permission
     * 
     * @return mixed
     */
    public function addPermission(...$permission)
    {
        if($permissions = self::mediator($permission))
            return $this->permissions()->saveMany($permissions);
    }

    /**
     * @param $permission
     */
    public function removePermission(...$permission)
    {
        return self::detachPermissions(self::mediator($permission));
    }

    /**
     * @param $permissions
     */
    public function changePermission(...$permissions)
    {
        if(self::detachPermissions())
            return self::addPermission($permissions);
    }

    /**
     * @param $permission
     */
    public function detachPermissions($permission = null)
    {
        return $this->permissions()->detach($permission);
    }

    /**
     * @param $permission
     * @access private
     */
    private function mediator($permission)
    {
        return self::getPermissions(Arr::flatten($permission));
    }

    /**
     * @param $permissions
     * @access private
     * 
     * @return object
     */
    private function getPermissions(...$permissions)
    {
        return Permission::whereIn('slug', (array)$permissions)->get();
    }
}