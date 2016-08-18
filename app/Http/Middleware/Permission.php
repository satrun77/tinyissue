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
use Illuminate\Database\Eloquent\Model as ModelAbstract;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tinyissue\Contracts\Model\AccessControl;
use Tinyissue\Model\Permission as PermissionModel;
use Tinyissue\Model\Project as ProjectModel;
use Tinyissue\Model\User;

/**
 * Permission is a Middleware class to for checking if current user has the permission to access the request.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Permission extends MiddlewareAbstract
{
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
        try {
            $user = $this->getLoggedUser();
        } catch (\Exception $e) {
            return false;
        }

        // Can access all projects
        if ($permission !== PermissionModel::PERM_ADMIN && $user->permission(PermissionModel::PERM_PROJECT_ALL)) {
            return true;
        }

        $hasPermission = $user->permission($permission);

        // Can access the current context
        $context       = $this->getCurrentContext($request->route());
        $contextAccess = true;
        if ($context instanceof AccessControl) {
            $contextAccess = $context->can($permission, $user);
            if (!$contextAccess) {
                return false;
            }

            return true;
        }

        return $hasPermission;
    }

    /**
     * Return the model object of the current context.
     * We check the lowest ( Comment ) first, to the highest ( Project ).
     *
     * @param Route $route
     *
     * @return ModelAbstract|null
     */
    protected function getCurrentContext(Route $route)
    {
        foreach ($this->contexts as $context) {
            $parameter = $route->getParameter($context);
            if ($parameter instanceof ModelAbstract) {
                return $parameter;
            }
        }

        return null;
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
