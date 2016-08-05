<?php
/**
 * Routes for logged in users such as, dashboard.
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */
$router->get('dashboard', 'HomeController@getDashboard');
