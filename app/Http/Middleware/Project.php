<?php

namespace Tinyissue\Http\Middleware;

use Closure;

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
            if ($issue->project_id !== $project->id) {
                return response('Unauthorized.', 401);
            }
        } else if ($project === null && $issue && $request->route()->getUri() === 'project/issue/{issue}') {
            $request->route()->forgetParameter('issue');
            $request->route()->setParameter('project', $issue->project);
            $request->route()->setParameter('issue', $issue);
        } else if ($project && $note) {
            if ($note->project_id !== $project->id) {
                return response('Unauthorized.', 401);
            }
        }

        return $next($request);
    }
}
