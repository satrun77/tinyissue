<?php
/**
 * Routes for user views.
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */
$router->get('user/issues/{display?}/{project?}', 'UserController@getIssues');
$router->get('user/settings/messages', 'UserController@getMessagesSettings');
$router->post('user/settings/messages', 'UserController@postMessagesSettings');
$router->get('user/settings', 'UserController@getSettings');
$router->post('user/settings', 'UserController@postSettings');
