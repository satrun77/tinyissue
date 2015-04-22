<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

/**
 * Permission is a Middleware class to for checking if current user has the permission to access the request
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
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
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $permission = $this->getPermission($request);
        $user = $this->auth->user();
        // Check if user has the permission
        // & if the user can access the current context (e.g. is one of the project users)
        if (!$user->permission($permission) || !$user->permissionInContext($request->route()->parameters())) {
            abort(401);
        }

        return $next($request);
    }

    /**
     * Returns the permission defined in route action
     *
     * @param Request $request
     *
     * @return mixed
     */
    protected function getPermission(Request $request)
    {
        $actions = $request->route()->getAction();

        return $actions['permission'];
    }
}
