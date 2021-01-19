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
    public function handle($request, Closure $next, $permissions)
    {
        if(auth()->guest()) 
            abort(403);

        foreach(explode('|', $permissions) as $permission)
            if(!auth()->user()->hasPermission($permission))
                abort(403);

        return $next($request);
    }
}