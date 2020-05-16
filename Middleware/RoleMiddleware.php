<?php
namespace CodeForms\Repositories\Crew\Middleware;

use Closure;
/**
 * 
 */
class RoleMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @param $role
     * 
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (auth()->guest() or !$request->user()->hasRole(explode('|', $role))) 
           abort(403);

        return $next($request);
    }
}
