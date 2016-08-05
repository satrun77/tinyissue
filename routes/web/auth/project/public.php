<?php
/**
 * Routes for some of project views that could be publicly available.
 * The routes here are included if public projects disabled.
 */

/** @var \Illuminate\Routing\Router $router */
/** @var \Tinyissue\Providers\RouteServiceProvider $this */
if (!app('tinyissue.settings')->isPublicProjectsEnabled()) {
    require base_path('routes/' . $directory . '/public/project.php');
}
