<?php
namespace CodeForms\Repositories\Crew\Traits;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Collection;
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
     * @param $permissions
     * 
     * @return mixed
     */
    public function setPermission(...$permissions)
    {
        $permissions = Arr::flatten($permissions);

        return $this->permissions()->sync(self::permissionCollection($permissions));
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
     * @param array $permissions
     * @access private
     * 
     * @return object
     */
    private function permissionCollection($permissions): object
    {
        $collection = new Collection;

        foreach($permissions as $permission)
            $package = $collection->push(self::getPermissions($permission));

        return $package->filter(function ($result) {
            return is_object($result);
        });
    }

    /**
     * @param $permission
     * @access private
     * 
     * @return object
     */
    private function getPermissions($permission)
    {
        return Permission::where('slug', $permission)->first();
    }
}