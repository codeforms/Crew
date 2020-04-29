<?php
namespace CodeForms\Repositories\Crew\Middleware;

use Closure;
/**
 * 
 */
class PermissionMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @param $permission
     * 
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        if(auth()->guest()) 
            abort(403);
        
        $permissions = is_array($permission) ? $permission : explode('|', $permission);

        foreach($permissions as $permission)
            if(auth()->user()->hasPermission($permission))
                return $next($request);

        abort(403);
    }
}