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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Tinyissue\Model\Project as ProjectModel;

/**
 * Project is a Middleware class to for checking if the route parameters are correct.
 * e.g. The issue id is belongs to the project id.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Project extends MiddlewareAbstract
{
    /**
     * List of callbacks to handle the incoming request.
     *
     * @var array
     */
    protected $callbacks = [
        'Issue',
        'IssueFilter',
        'Note',
        'Project',
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
        // Current callback
        $callback = current($this->callbacks);
        $method   = 'handle' . $callback . 'Request';

        if ($callback && !$this->$method($request)) {

            // Current callback does not belong to the request - move next
            next($this->callbacks);

            return $this->handle($request, $next);
        }

        return $next($request);
    }

    /**
     * Whether or not the incoming request is valid project issue request.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function handleIssueRequest(Request $request)
    {
        return $this->isBelongToProject($request, 'issue');
    }

    /**
     * Whether or not the incoming uri is for the issue filter "project/issue/{issue}".
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function handleIssueFilterRequest(Request $request)
    {
        /** @var ProjectModel|null $project */
        $project = $request->route()->parameter('project');
        /** @var ProjectModel\Issue|null $issue */
        $issue = $request->route()->parameter('issue');

        if ($project === null && $issue && $request->route()->uri() === 'project/issue/{issue}') {
            // Load the project from the issue model
            $request->route()->forgetParameter('issue');
            $request->route()->setParameter('project', $issue->project);
            $request->route()->setParameter('issue', $issue);

            return true;
        }

        if ($request->route()->uri() === 'project/issue/{issue_no}') {
            $issueNo = $request->route()->parameter('issue_no');
            $segments = explode('-', $issueNo);
            $project = ProjectModel::getByKey($segments[0]);
            if ($project instanceof ProjectModel) {
                $issue = $project->issues()->number($segments[1])->limit(1)->first();
                if ($issue instanceof ProjectModel\Issue) {
                    $issue->setRelation('project', $project);
                    $request->route()->forgetParameter('issue_no');
                    $request->route()->setParameter('project', $project);
                    $request->route()->setParameter('issue', $issue);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Whether or not the incoming request is valid project note request.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function handleNoteRequest(Request $request)
    {
        return $this->isBelongToProject($request, 'note');
    }

    /**
     * Whether or not the incoming request is valid project request.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function handleProjectRequest(Request $request)
    {
        /** @var ProjectModel|null $project */
        $project = $request->route()->parameter('project');

        return $project instanceof ProjectModel;
    }

    /**
     * Whether or not a model entity relationship with the project is correct.
     *
     * @param Request $request
     * @param string  $entityName
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return bool
     */
    protected function isBelongToProject(Request $request, $entityName)
    {
        /** @var Model $entity */
        $entity = $request->route()->parameter($entityName);

        /** @var ProjectModel|null $project */
        $project = $request->route()->parameter('project');

        if (!$entity instanceof Model || !$project instanceof ProjectModel) {
            return false;
        }

        // Abort request invalid data
        if ((int) $entity->project_id !== (int) $project->id) {
            abort(401);
        }

        // Add project instance to the entity
        $entity->setRelation('project', $project);

        return true;
    }
}
