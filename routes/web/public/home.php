<?php
/**
 * Routes for public pages when public project enabled.
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */

// View issues
$router->get('issues', 'HomeController@getIssues');
