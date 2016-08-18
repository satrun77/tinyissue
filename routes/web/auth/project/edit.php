<?php
/**
 * Routes for project edit views.
 */
use Tinyissue\Model\Project\Note;

/* @var \Illuminate\Routing\Router $router */
/* @var \Tinyissue\Providers\RouteServiceProvider $this */
$router->group(['middleware' => 'can:update,project'], function ($router) {
    $router->get('project/{project}/edit', 'ProjectController@getEdit');
    $router->post('project/{project}/edit', 'ProjectController@postEdit');
    $router->post('project/{project}/unassign_user', ['middleware' => 'ajax', 'uses' => 'ProjectController@postUnassign']);
    $router->post('project/{project}/assign_user', ['middleware' => 'ajax', 'uses' => 'ProjectController@postAssign']);
});

$router->get('project/inactive_users/{project?}', ['middleware' => 'ajax', 'uses' => 'ProjectController@getInactiveUsers']);

$router->group(['middleware' => 'can:export,project'], function ($router) {
    $router->post('project/{project}/export_issues', ['middleware' => 'ajax', 'uses' => 'ProjectController@postExportIssues']);
    $router->get('project/{project}/download_export/{file}', ['uses' => 'ProjectController@getDownloadExport']);
});

$router->group(['middleware' => 'can:update,note,project'], function ($router) {
    // Edit project notes
    $router->post('project/{project}/edit_note/{note}', ['middleware' => 'ajax', 'uses' => 'ProjectController@postEditNote']);
    $router->get('project/{project}/delete_note/{note}', ['middleware' => 'ajax', 'uses' => 'ProjectController@getDeleteNote']);
});

$router->group(['middleware' => 'can:create,' . Note::class . ',project'], function ($router) {
    $router->post('project/{project}/add_note', 'ProjectController@postAddNote');
});
