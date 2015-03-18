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

/**
 * Project is a Middleware class to for checking if the route parameters are correct.
 * e.g. The issue id is belongs to the project id.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Project
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $project = $request->route()->getParameter('project');
        $issue = $request->route()->getParameter('issue');
        $note = $request->route()->getParameter('note');
        if ($project && $issue) {
            // Inside an issue page, the loaded issue must belong to the loaded project
            if ($issue->project_id !== $project->id) {
                return abort(401);
            }
        } elseif ($project === null && $issue && $request->route()->getUri() === 'project/issue/{issue}') {
            // Requesting an issue page without a project id in the url
            // Then load the project from the issue model
            $request->route()->forgetParameter('issue');
            $request->route()->setParameter('project', $issue->project);
            $request->route()->setParameter('issue', $issue);
        } elseif ($project && $note) {
            // Inside a note page, the loaded note must belong to the loaded project
            if ($note->project_id !== $project->id) {
                return abort(401);
            }
        }

        return $next($request);
    }
}
