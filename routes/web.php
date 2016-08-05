<?php
/**
 * Routes for web views.
 *
 * @permission auth
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */

// Public views
require base_path('routes/' . $directory . '/home.php');
require base_path('routes/' . $directory . '/public.php');

// Authorise views
$router->group(['middleware' => 'auth'], function ($router) use ($directory) {
    // User area, dashboard, & projects
    require base_path('routes/' . $directory . '/auth/dashboard.php');
    require base_path('routes/' . $directory . '/auth/user.php');
    require base_path('routes/' . $directory . '/auth/projects.php');

    // Project area
    require base_path('routes/' . $directory . '/auth/project.php');

    // Admin area
    require base_path('routes/' . $directory . '/auth/admin/admin.php');
});
