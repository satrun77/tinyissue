<?php
/**
 * Routes for projects views.
 *
 * @permission project-create
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */

// Only load if system public projects disabled
if (!app('tinyissue.settings')->isPublicProjectsEnabled()) {
    require base_path('routes/' . $directory . '/public/projects.php');
}

// Global Add issue
$router->get('projects/new_issue', 'ProjectsController@getNewIssue');
$router->post('projects/new_issue', 'ProjectsController@postNewIssue');

// Create project
$router->group(['middleware' => 'permission', 'permission' => 'project-create'], function ($router) {
    $router->get('projects/new', 'ProjectsController@getNew');
    $router->post('projects/new', 'ProjectsController@postNew');
});
