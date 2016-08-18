<?php
/**
 * Routes for project create issue.
 */
use Tinyissue\Model\Project\Issue;

/* @var \Illuminate\Routing\Router $router */
/* @var \Tinyissue\Providers\RouteServiceProvider $this */
$router->group(['middleware' => 'can:create,' . Issue::class . ',project'], function ($router) {
    $router->get('project/{project}/issue/new', 'Project\IssueController@getNew');
    $router->post('project/{project}/issue/new', 'Project\IssueController@postNew');
});
