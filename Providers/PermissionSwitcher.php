<?php
namespace CodeForms\Repositories\Crew\Providers;

use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use CodeForms\Repositories\Crew\Models\Permission;
/**
 * 
 */
class PermissionSwitcher
{
    /**
     * @var Gate
     */
    protected $gate;

    /**
     * @param Gate $gate
     */
    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }

    /**
     * @return bool
     */
    public function registerPermissions()
    {
        try {
            $this->getPermissions()->map(function ($permission) {
                $this->gate->define($permission->slug, function ($user) use ($permission) {
                    return $user->hasPermission($permission->slug);
                });
            });

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPermissions()
    {
        return Permission::with('roles')->get();
    }
}
