<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Middleware;

use Closure;
use Tinyissue\Model\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Contracts\Auth\Guard;
use Tinyissue\Model\Project as ProjectModel;
use Tinyissue\Model\Permission as PermissionModel;
use Illuminate\Database\Eloquent\Model as ModelAbstract;

/**
 * Permission is a Middleware class to for checking if current user has the permission to access the request.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Permission
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * List of permissions that can be accessed by public users.
     *
     * @var array
     */
    protected $publicAccess = [
        'issue-view',
    ];

    /**
     * Ordered list of contexts.
     *
     * @var array
     */
    protected $contexts = [
        'comment',
        'attachment',
        'issue',
        'project',
    ];

    /**
     * Create a new filter instance.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $permission = $this->getPermission($request);

        // Can't access if public project disabled or user does not have access
        if (!$this->isInPublicProjectContext($request, $permission) && !$this->canAccess($request, $permission)) {
            abort(401);
        }

        return $next($request);
    }

    /**
     * Whether or not the current context is in public project.
     *
     * @param Request $request
     * @param string  $permission
     *
     * @return bool
     */
    protected function isInPublicProjectContext(Request $request, $permission)
    {
        /** @var ProjectModel|null $project */
        $project         = $request->route()->getParameter('project');
        $isPublicEnabled = app('tinyissue.settings')->isPublicProjectsEnabled();
        $isPublicAccess  = in_array($permission, $this->publicAccess);
        $isPublicProject = $project instanceof ProjectModel && $project->isPublic();

        return $isPublicEnabled && $isPublicAccess && $isPublicProject;
    }

    /**
     * Whether or not the user can access the current context.
     *
     * @param Request $request
     * @param string  $permission
     *
     * @return bool
     */
    protected function canAccess(Request $request, $permission)
    {
        $user = $this->auth->user();

        return !(!is_null($user) && (!$user->permission($permission) || !$this->canAccessContext($user, $request->route(), $permission)));
    }

    /**
     * Whether or not the user has a valid permission in current context
     * e.g. can access the issue or the project.
     *
     * @param User   $user
     * @param Route  $route
     * @param string $permission
     *
     * @return bool
     */
    public function canAccessContext(User $user, Route $route, $permission)
    {
        // Can access all projects
        if ($user->permission(PermissionModel::PERM_PROJECT_ALL)) {
            return true;
        }

        // Can access the current context
        $context = $this->getCurrentContext($route);
        $action  = $permission == PermissionModel::PERM_ISSUE_MODIFY ? 'canEdit' : 'canView';

        return $context->$action($user);
    }

    /**
     * Return the model object of the current context.
     * We check the lowest ( Comment ) first, to the highest ( Project ).
     *
     * @param Route $route
     *
     * @return ModelAbstract
     */
    protected function getCurrentContext(Route $route)
    {
        foreach ($this->contexts as $context) {
            $parameter = $route->getParameter($context);
            if ($parameter instanceof ModelAbstract) {
                return $parameter;
            }
        }

        return $route->getParameter('project');
    }

    /**
     * Returns the permission defined in route action.
     *
     * @param Request $request
     *
     * @return mixed
     */
    protected function getPermission(Request $request)
    {
        $actions = $request->route()->getAction();

        return $actions['permission'];
    }
}
