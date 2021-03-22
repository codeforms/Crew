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
        return self::hasRolePermission($permission) or self::hasThisPermission($permission);
    }

    /**
     * @param $permission
     * 
     * @return boolean
     */
    public function hasRolePermission($permission): bool
    {
        if(!isset($this->roles))
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
    private function hasThisPermission($permission): bool
    {
        return !$this->permissions->where('slug', $permission)->isEmpty();
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
            $package = $collection->push(self::getPermission($permission));

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
    private function getPermission($permission)
    {
        return Permission::where('slug', $permission)->first();
    }
}