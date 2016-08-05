<?php
/**
 * Routes for public pages when public project enabled.
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */
if (app('tinyissue.settings')->isPublicProjectsEnabled()) {
    require base_path('routes/' . $directory . '/public/home.php');
    require base_path('routes/' . $directory . '/public/projects.php');
    $router->group(['middleware' => 'project'], function ($router) use ($directory) {
    require base_path('routes/' . $directory . '/public/project.php');
});
}
