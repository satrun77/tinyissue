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
 * Locale is a Middleware class to set the locale for the current logged in user.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Locale extends MiddlewareAbstract
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
        if (!$this->getAuth()->guest()) {
            app()->setLocale($this->getLoggedUser()->language);
        }

        return $next($request);
    }
}
