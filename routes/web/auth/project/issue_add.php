<?php
/**
 * Routes for project create issue.
 *
 * @permission issue-create
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */
$router->group(['middleware' => 'permission', 'permission' => 'issue-create'], function ($router) {
    $router->get('project/{project}/issue/new', 'Project\IssueController@getNew');
    $router->post('project/{project}/issue/new', 'Project\IssueController@postNew');
});
