<?php
/**
 * Routes for some of project views.
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */
$router->get('project/{project}/assigned', 'ProjectController@getAssigned');
$router->get('project/{project}/created', 'ProjectController@getCreated');
