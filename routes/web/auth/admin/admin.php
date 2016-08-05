<?php
/**
 * Routes for administration views.
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */
$router->group(['middleware' => 'permission', 'permission' => 'administration'], function ($router) use ($directory) {
    // Index
    $router->get('administration', 'AdministrationController@getIndex');

    // Users
    $router->get('administration/users', 'Administration\UsersController@getIndex');
    $router->get('administration/users/add', 'Administration\UsersController@getAdd');
    $router->post('administration/users/add', 'Administration\UsersController@postAdd');
    $router->get('administration/users/edit/{user}', 'Administration\UsersController@getEdit');
    $router->post('administration/users/edit/{user}', 'Administration\UsersController@postEdit');
    $router->get('administration/users/delete/{user}', 'Administration\UsersController@getDelete');

    // Tags
    $router->get('administration/tags', 'Administration\TagsController@getIndex');
    $router->get('administration/tag/new', 'Administration\TagsController@getNew');
    $router->post('administration/tag/new', 'Administration\TagsController@postNew');
    $router->get('administration/tag/{tag}/edit', 'Administration\TagsController@getEdit');
    $router->post('administration/tag/{tag}/edit', 'Administration\TagsController@postEdit');
    $router->get('administration/tag/{tag}/delete', 'Administration\TagsController@getDelete');

    // Settings
    $router->get('administration/settings', 'AdministrationController@getSettings');
    $router->post('administration/settings', 'AdministrationController@postSettings');
    $router->get('administration/settings/maintenance', 'AdministrationController@getChangeMaintenanceMode');
});
