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
        if(auth()->guest()) 
            abort(403);

        $roles = explode(',', preg_replace("/\|/", ",", $role));

        if ($request->user()->hasRole($roles))
            return $next($request);
        
        abort(403);
    }
}
