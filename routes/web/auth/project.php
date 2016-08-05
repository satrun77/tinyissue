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
