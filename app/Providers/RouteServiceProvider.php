<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * RouteServiceProvider is the route service provider for registering the application routes to controllers and actions.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Tinyissue\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function boot(Router $router)
    {
        $router->model('project', 'Tinyissue\Model\Project');
        $router->model('issue', 'Tinyissue\Model\Project\Issue');
        $router->model('attachment', 'Tinyissue\Model\Project\Issue\Attachment');
        $router->model('comment', 'Tinyissue\Model\Project\Issue\Comment');
        $router->model('note', 'Tinyissue\Model\Project\Note');
        $router->model('tag', 'Tinyissue\Model\Tag');
        $router->model('user', 'Tinyissue\Model\User');

        $router->pattern('project', '[0-9]+');
        $router->pattern('issue', '[0-9]+');
        $router->pattern('comment', '[0-9]+');
        $router->pattern('issue', '[0-9]+');
        $router->pattern('limit', '[0-9]+');
        $router->pattern('attachment', '[0-9]+');
        $router->pattern('note', '[0-9]+');
        $router->pattern('term', '\w+');
        $router->pattern('tag', '[0-9]+');

        parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function (Router $router) {
            $router->get('/', 'HomeController@getIndex');
            $router->get('logout', 'HomeController@getLogout');
            $router->post('signin', 'HomeController@postSignin');

            if (app('tinyissue.settings')->isPublicProjectsEnabled()) {
                $this->addPublicRoutes($router);
            }

            $router->group(['middleware' => 'auth'], function (Router $router) {
                $router->get('dashboard', 'HomeController@getDashboard');

                // Login user area
                $router->get('user/issues/{display?}/{project?}', 'UserController@getIssues');
                $router->controller('user', 'UserController');

                // Projects area
                if (!app('tinyissue.settings')->isPublicProjectsEnabled()) {
                    $this->addPublicProjectsRoutes($router);
                }
                $router->get('projects/new_issue', 'ProjectsController@getNewIssue');
                $router->post('projects/new_issue', 'ProjectsController@postNewIssue');
                $router->group(['middleware' => 'permission', 'permission' => 'project-create'], function (Router $router) {
                    $router->get('projects/new', 'ProjectsController@getNew');
                    $router->post('projects/new', 'ProjectsController@postNew');
                });

                $router->group(['middleware' => 'project'], function (Router $router) {
                    if (!app('tinyissue.settings')->isPublicProjectsEnabled()) {
                        $this->addPublicProjectRoutes($router);
                    }

                    // View project
                    $router->get('project/{project}/assigned', 'ProjectController@getAssigned');

                    // Edit project
                    $router->group(['middleware' => 'permission', 'permission' => 'project-modify'], function (Router $router) {
                        $router->get('project/{project}/edit', 'ProjectController@getEdit');
                        $router->post('project/{project}/edit', 'ProjectController@postEdit');
                        $router->get('project/inactive_users/{project?}', ['middleware' => 'ajax', 'uses' => 'ProjectController@getInactiveUsers']);
                        $router->post('project/{project}/unassign_user', ['middleware' => 'ajax', 'uses' => 'ProjectController@postUnassign']);
                        $router->post('project/{project}/assign_user', ['middleware' => 'ajax', 'uses' => 'ProjectController@postAssign']);
                        $router->post('project/{project}/export_issues', ['middleware' => 'ajax', 'uses' => 'ProjectController@postExportIssues']);
                        $router->get('project/{project}/download_export/{file}', ['uses' => 'ProjectController@getDownloadExport']);

                        // Edit project notes
                        $router->post('project/{project}/edit_note/{note}', ['middleware' => 'ajax', 'uses' => 'ProjectController@postEditNote']);
                        $router->get('project/{project}/delete_note/{note}', ['middleware' => 'ajax', 'uses' => 'ProjectController@getDeleteNote']);
                        $router->post('project/{project}/add_note', 'ProjectController@postAddNote');
                    });

                    // Add issue
                    $router->group(['middleware' => 'permission', 'permission' => 'issue-create'], function (Router $router) {
                        $router->get('project/{project}/issue/new', 'Project\IssueController@getNew');
                        $router->post('project/{project}/issue/new', 'Project\IssueController@postNew');
                    });

                    // Edit issue
                    $router->group(['middleware' => 'permission', 'permission' => 'issue-modify'], function (Router $router) {
                        $router->get('project/{project}/issue/{issue}/edit', 'Project\IssueController@getEdit');
                        $router->post('project/{project}/issue/{issue}/edit', 'Project\IssueController@postEdit');
                        $router->post('project/issue/{issue}/assign', ['middleware' => 'ajax', 'uses' => 'Project\IssueController@postAssign']);
                        $router->get('project/{project}/issue/{issue}/status/{status?}', 'Project\IssueController@getClose')->where('status', '[0-1]');
                        $router->post('project/{project}/issue/upload_attachment', 'Project\IssueController@postUploadAttachment');
                        $router->post('project/{project}/issue/remove_attachment', 'Project\IssueController@postRemoveAttachment');
                        $router->post('project/issue/{issue}/change_project', 'Project\IssueController@postChangeProject');
                        $router->post('project/issue/{issue}/change_kanban_tag', ['uses' => 'Project\IssueController@postChangeKanbanTag']);

                        // Edit comment
                        $router->post('project/issue/edit_comment/{comment}', ['middleware' => 'ajax', 'uses' => 'Project\IssueController@postEditComment']);
                        $router->get('project/issue/delete_comment/{comment}', ['middleware' => 'ajax', 'uses' => 'Project\IssueController@getDeleteComment']);
                        $router->post('project/{project}/issue/{issue}/add_comment', 'Project\IssueController@getAddComment');
                    });
                });

                // Admin area
                $router->group(['middleware' => 'permission', 'permission' => 'administration'], function (Router $router) {
                    $router->get('administration', 'AdministrationController@getIndex');
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

                    // Settings
                    $router->get('administration/settings', 'AdministrationController@getSettings');
                    $router->post('administration/settings', 'AdministrationController@postSettings');
                });
            });
        });
    }

    /**
     * All of the routes that can be made public.
     *
     * @param Router $router
     *
     * @return void
     */
    protected function addPublicRoutes(Router $router)
    {
        // View issues
        $router->get('issues', 'HomeController@getIssues');

        // View projects
        $this->addPublicProjectsRoutes($router);

        $router->group(['middleware' => 'project'], function (Router $router) {
            $this->addPublicProjectRoutes($router);
        });
    }

    /**
     * Routes related to projects controller that can be made public or private.
     *
     * @param Router $router
     *
     * @return void
     */
    protected function addPublicProjectsRoutes(Router $router)
    {
        $router->post('projects/progress', ['middleware' => 'ajax', 'uses' => 'ProjectsController@postProgress']);
        $router->get('projects/{status?}', 'ProjectsController@getIndex')->where('status', '[0-1]');
    }

    /**
     * Routes related to project (issue, comment, notes, etc..) that can be made public or private.
     *
     * @param Router $router
     *
     * @return void
     */
    protected function addPublicProjectRoutes(Router $router)
    {
        // Tags autocomplete
        $router->get('administration/tags/suggestions/{term?}', ['middleware' => 'ajax', 'uses' => 'Administration\TagsController@getTags']);

        // View project
        $router->get('project/{project}', 'ProjectController@getIndex')->where('project', '[0-9]+');
        $router->get('project/{project}/issues/{status?}', 'ProjectController@getIssues')->where('status', '[0-1]')->where('project', '[0-9]+');
        $router->get('project/{project}/notes', 'ProjectController@getNotes')->where('project', '[0-9]+');

        // View issue
        $router->group(['middleware' => 'permission', 'permission' => 'issue-view'], function (Router $router) {
            $router->get('project/issue/{issue}', 'Project\IssueController@getIndex');
            $router->get('project/{project}/issue/{issue}', 'Project\IssueController@getIndex');
            $router->get('project/{project}/issue/{issue}/download/{attachment}', 'Project\IssueController@getDownloadAttachment');
            $router->get('project/{project}/issue/{issue}/display/{attachment}', 'Project\IssueController@getDisplayAttachment');
        });
    }
}
