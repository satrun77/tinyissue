<?php
/**
 * Routes for login views.
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */
$router->get('/', 'HomeController@getIndex');
$router->get('logout', 'HomeController@getLogout');
$router->post('signin', 'HomeController@postSignin');
