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
use Illuminate\Http\Request;

/**
 * Authenticate is a Middleware class to for checking if current user is logged in.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Authenticate extends MiddlewareAbstract
{
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
        if ($this->getAuth()->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->guest('/');
        }

        return $next($request);
    }
}
