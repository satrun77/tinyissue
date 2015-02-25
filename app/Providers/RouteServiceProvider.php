<?php

namespace Tinyissue\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

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
        parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router) {
            $router->get("/", "HomeController@getIndex");
            $router->get("logout", "HomeController@getLogout");
            $router->post("signin", "HomeController@postSignin");

            $router->group(['middleware' => 'auth'], function ($router) {
                $router->get('dashboard', 'HomeController@getDashboard');

                // Login user area
                $router->controller('user', 'UserController');

                // Projects area
                $router->get('projects/{status?}', 'ProjectsController@getIndex')->where('status', '[0-1]');
                $router->get('projects/new', 'ProjectsController@getNew');
                $router->post('projects/new', 'ProjectsController@postNew');

                $router->group(['middleware' => 'project'], function ($router) {
                    $router->model('project', 'Tinyissue\Model\Project');
                    $router->model('attachment', 'Tinyissue\Model\Project\Issue\Attachment');
                    $router->pattern('comment', '[0-9]+');
                    $router->pattern('issue', '[0-9]+');
                    $router->pattern('limit', '[0-9]+');
                    $router->model('comment', 'Tinyissue\Model\Project\Issue\Comment');
                    $router->pattern('project', '[0-9]+');
                    $router->pattern('attachment', '[0-9]+');
                    $router->pattern('note', '[0-9]+');
                    $router->model('note', 'Tinyissue\Model\Project\Note');

                    // View project
                    $router->get('project/{project}', 'ProjectController@getIndex');
                    $router->get('project/{project}/issues/{status?}', 'ProjectController@getIssues')->where('status', '[0-1]');
                    $router->get('project/{project}/assigned', 'ProjectController@getAssigned');
                    $router->get('project/{project}/notes', 'ProjectController@getNotes');

                    // Edit project
                    $router->group(['middleware' => 'permission', 'permission' => 'project-modify'], function ($router) {
                        $router->get('project/{project}/edit', 'ProjectController@getEdit');
                        $router->post('project/{project}/edit', 'ProjectController@postEdit');
                        $router->get('project/inactive_users/{project?}', array('middleware' => 'ajax', 'uses' => 'ProjectController@getInactiveUsers'));
                        $router->post('project/{project}/unassign_user', array('middleware' => 'ajax', 'uses' => 'ProjectController@postUnassign'));
                        $router->post('project/{project}/assign_user', array('middleware' => 'ajax', 'uses' => 'ProjectController@postAssign'));

                        // Edit project notes
                        $router->post('project/{project}/edit_note/{note}', ['middleware' => 'ajax', 'uses' => 'ProjectController@postEditNote']);
                        $router->get('project/{project}/delete_note/{note}', ['middleware' => 'ajax', 'uses' => 'ProjectController@getDeleteNote']);
                        $router->post('project/{project}/add_note', 'ProjectController@postAddNote');
                    });

                    // Add issue
                    $router->get('project/{project}/issue/new', 'Project\IssueController@getNew');
                    $router->post('project/{project}/issue/new', 'Project\IssueController@postNew');

                    // View issue
                    $router->model('issue', 'Tinyissue\Model\Project\Issue');
                    $router->get('project/{project}/issue/{issue}', 'Project\IssueController@getIndex');
                    $router->get('project/issue/{issue}', 'Project\IssueController@getIndex');
                    $router->get('project/{project}/issue/{issue}/download/{attachment}', 'Project\IssueController@getDownloadAttachment');
                    $router->get('project/{project}/issue/{issue}/display/{attachment}', 'Project\IssueController@getDisplayAttachment');

                    // Edit issue
                    $router->group(array('middleware' => 'permission', 'permission' => 'issue-modify'), function ($router) {
                        $router->get('project/{project}/issue/{issue}/edit', 'Project\IssueController@getEdit');
                        $router->post('project/{project}/issue/{issue}/edit', 'Project\IssueController@postEdit');
                        $router->post('project/issue/{issue}/assign', ['middleware' => 'ajax', 'uses' => 'Project\IssueController@postAssign']);
                        $router->get('project/{project}/issue/{issue}/status/{status?}', 'Project\IssueController@getClose')->where('status', '[0-1]');
                        $router->post('project/{project}/issue/upload_attachment', 'Project\IssueController@postUploadAttachment');
                        $router->post('project/{project}/issue/remove_attachment', 'Project\IssueController@postRemoveAttachment');
                        $router->post('project/issue/{issue}/change_project', 'Project\IssueController@postChangeProject');

                        // Edit comment
                        $router->post('project/issue/edit_comment/{comment}', ['middleware' => 'ajax', 'uses' => 'Project\IssueController@postEditComment']);
                        $router->get('project/issue/delete_comment/{comment}', ['middleware' => 'ajax', 'uses' => 'Project\IssueController@getDeleteComment']);
                        $router->post('project/{project}/issue/{issue}/add_comment', 'Project\IssueController@getAddComment');
                    });
                });

                // Admin area
                $router->group(['middleware' => 'permission', 'permission' => 'administration'], function ($router) {
                    $router->model('user', 'Tinyissue\Model\User');
                    $router->get('administration', 'AdministrationController@getIndex');
                    $router->get('administration/users', 'Administration\UsersController@getIndex');
                    $router->get('administration/users/add', 'Administration\UsersController@getAdd');
                    $router->post('administration/users/add', 'Administration\UsersController@postAdd');
                    $router->get('administration/users/edit/{user}', 'Administration\UsersController@getEdit');
                    $router->post('administration/users/edit/{user}', 'Administration\UsersController@postEdit');
                    $router->get('administration/users/delete/{user}', 'Administration\UsersController@getDelete');
                });
            });
        });
    }
}
