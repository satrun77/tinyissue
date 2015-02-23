<?php

namespace Tinyissue\Http\Middleware;

use Closure;

class VerifyAjaxRequest
{
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
        if (!$request->ajax()) {
            return response('Not found', 404);
        }

        return $next($request);
    }
}
