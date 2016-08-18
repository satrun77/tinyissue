<?php
/**
 * Routes for project views.
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */
$router->group(['middleware' => 'project'], function ($router) use ($directory) {
    require base_path('routes/' . $directory . '/auth/project/public.php');
    require base_path('routes/' . $directory . '/auth/project/view.php');
    require base_path('routes/' . $directory . '/auth/project/edit.php');
    require base_path('routes/' . $directory . '/auth/project/issue_add.php');
    require base_path('routes/' . $directory . '/auth/project/issue_edit.php');
});

$router->group(['middleware' => 'can:view,project'], function ($router) {
    // View project
    $router->get('project/{project}', 'ProjectController@getIndex')->where('project', '[0-9]+');
    $router->get('project/{project}/issues/{status?}', 'ProjectController@getIssues')->where('status', '[0-1]')->where('project', '[0-9]+');
    $router->get('project/{project}/notes', 'ProjectController@getNotes')->where('project', '[0-9]+');
});
