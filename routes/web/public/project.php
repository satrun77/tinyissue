<?php
/**
 * Routes for public pages when public project enabled.
 * Related to project views.
 *
 * @permission issue-view
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */

// View project
$router->get('project/{project}', 'ProjectController@getIndex')->where('project', '[0-9]+');
$router->get('project/{project}/issues/{status?}', 'ProjectController@getIssues')->where('status', '[0-1]')->where('project', '[0-9]+');
$router->get('project/{project}/notes', 'ProjectController@getNotes')->where('project', '[0-9]+');

// View issue
$router->group(['middleware' => 'permission', 'permission' => 'issue-view'], function ($router) {
    $router->get('project/issue/{issue}', 'Project\IssueController@getIndex');
    $router->get('project/{project}/issue/{issue}', 'Project\IssueController@getIndex');
    $router->get('project/{project}/issue/{issue}/comments', ['middleware' => 'ajax', 'uses' => 'Project\IssueController@getIssueComments']);
    $router->get('project/{project}/issue/{issue}/activity', ['middleware' => 'ajax', 'uses' => 'Project\IssueController@getIssueActivity']);
    $router->get('project/{project}/issue/{issue}/download/{attachment}', 'Project\IssueController@getDownloadAttachment');
    $router->get('project/{project}/issue/{issue}/display/{attachment}', 'Project\IssueController@getDisplayAttachment');
});
