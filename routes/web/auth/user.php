<?php
/**
 * Routes for user views.
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */
$router->get('user/issues', 'UserController@getListIssues');
$router->get('user/issues/kanban/{project?}', 'UserController@getKanbanIssues');
$router->get('user/settings/messages', 'UserController@getMessagesSettings');
$router->post('user/settings/messages', 'UserController@postMessagesSettings');
$router->get('user/settings', 'UserController@getSettings');
$router->post('user/settings', 'UserController@postSettings');
