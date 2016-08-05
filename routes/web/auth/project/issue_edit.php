<?php
/**
 * Routes for issue edit views.
 *
 * @permission issue-modify
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */

// Edit main issue
$router->group(['middleware' => 'permission', 'permission' => 'issue-modify'], function ($router) {
    $router->get('project/{project}/issue/{issue}/edit', 'Project\IssueController@getEdit');
    $router->post('project/{project}/issue/{issue}/edit', 'Project\IssueController@postEdit');
});

// Edit issue other details
$router->group(['middleware' => 'permission', 'permission' => 'issue-modify'], function ($router) {
    $router->post('project/issue/{issue}/assign', ['middleware' => 'ajax', 'uses' => 'Project\IssueController@postAssign']);
    $router->get('project/{project}/issue/{issue}/status/{status?}', 'Project\IssueController@getClose')->where('status', '[0-1]');
    $router->post('project/{project}/issue/upload_attachment', 'Project\IssueController@postUploadAttachment');
    $router->post('project/{project}/issue/remove_attachment', 'Project\IssueController@postRemoveAttachment');
    $router->post('project/issue/{issue}/change_project', 'Project\IssueController@postChangeProject');
    $router->post('project/issue/{issue}/change_kanban_tag', ['uses' => 'Project\IssueController@postChangeKanbanTag']);

    // Edit comment
    $router->post('project/issue/edit_comment/{comment}', ['middleware' => 'ajax', 'uses' => 'Project\IssueController@postEditComment']);
    $router->get('project/issue/delete_comment/{comment}', ['middleware' => 'ajax', 'uses' => 'Project\IssueController@getDeleteComment']);
    $router->post('project/{project}/issue/{issue}/add_comment', 'Project\IssueController@postAddComment');
    $router->get('project/{project}/issue/{issue}/delete/{attachment}', 'Project\IssueController@getDeleteAttachment');
});
