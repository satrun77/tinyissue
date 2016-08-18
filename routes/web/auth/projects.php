<?php
/**
 * Routes for projects views.
 */
use Tinyissue\Model\Project;

/* @var \Illuminate\Routing\Router $router */
/* @var \Tinyissue\Providers\RouteServiceProvider $this */

// Only load if system public projects disabled
if (!app('tinyissue.settings')->isPublicProjectsEnabled()) {
    require base_path('routes/' . $directory . '/public/projects.php');
}

$router->group(['middleware' => 'can:create,' . Project\Issue::class], function ($router) {
    // Global Add issue
    $router->get('projects/new_issue', 'ProjectsController@getNewIssue');
    $router->post('projects/new_issue', 'ProjectsController@postNewIssue');
});

// Create project
$router->group(['middleware' => 'can:create,' . Project::class], function ($router) {
    $router->get('projects/new', 'ProjectsController@getNew');
    $router->post('projects/new', 'ProjectsController@postNew');
});
