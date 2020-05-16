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

        foreach(explode('|', $permission) as $permission)
            if(auth()->user()->hasPermission($permission))
                return $next($request);

        abort(403);
    }
}