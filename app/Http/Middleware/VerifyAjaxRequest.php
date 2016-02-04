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
 * VerifyAjaxRequest is a Middleware class to limit request to Ajax call only.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class VerifyAjaxRequest
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->ajax()) {
            return response('Not found', 404);
        }

        return $next($request);
    }
}
