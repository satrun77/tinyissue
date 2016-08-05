<?php
/**
 * Routes for public pages when public project enabled.
 * Related to projects views.
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */
$router->post('projects/progress', ['middleware' => 'ajax', 'uses' => 'ProjectsController@postProgress']);
$router->get('projects/{status?}', 'ProjectsController@getIndex')->where('status', '[0-1]');
