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
 * Locale is a Middleware class to set the locale for the current logged in user.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Locale
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
        if (!$this->auth->guest()) {
            app()->setLocale($this->auth->user()->language);
        }

        return $next($request);
    }
}
