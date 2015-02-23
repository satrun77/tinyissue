<?php

namespace Tinyissue\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class Permission
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $permission = $this->getPermission($request);
        if (!$this->auth->user()->permission($permission)
            || !$this->auth->user()->permissionInContext($permission, $request->route()->parameters())
        ) {
            return response('Unauthorized.', 401);
        }

        return $next($request);
    }

    protected function getPermission($request)
    {
        $actions = $request->route()->getAction();

        return $actions['permission'];
    }
}
