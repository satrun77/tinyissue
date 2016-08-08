<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Controllers;

use Illuminate\Http\Request;
use Tinyissue\Form\Project as Form;
use Tinyissue\Form\GlobalIssue as IssueForm;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

/**
 * ProjectsController is the controller class for managing request related to projects.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class ProjectsController extends Controller
{
    /**
     * Display list of active/archived projects.
     *
     * @param int $status
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex($status = Project::STATUS_OPEN)
    {
        $viewData = [];
        if (!$this->auth->guest()) {
            $projects = $this->getLoggedUser()->projectsWithCountOpenIssues($status)->get();
            if ($status) {
                $countActive   = $projects->count();
                $countArchived = $this->getLoggedUser()->projectsWithCountOpenIssues(Project::STATUS_ARCHIVED)->count();
            } else {
                $countActive   = $this->getLoggedUser()->projectsWithCountOpenIssues(Project::STATUS_OPEN)->count();
                $countArchived = $projects->count();
            }
            $viewData['projects'] = $this->getLoggedUser()->projects()->get();
        } else {
            $viewData['sidebar'] = 'public';
            $project             = new Project();
            $projects            = $project->projectsWithOpenIssuesCount($status, Project::PRIVATE_NO)->get();
            if ($status) {
                $countActive   = $projects->count();
                $countArchived = $project->projectsWithOpenIssuesCount(Project::STATUS_ARCHIVED, Project::PRIVATE_NO)->count();
            } else {
                $countActive   = $project->projectsWithOpenIssuesCount(Project::STATUS_OPEN, Project::PRIVATE_NO)->count();
                $countArchived = $projects->count();
            }
            $user                    = new User();
            $viewData['activeUsers'] = $user->activeUsers();
        }
        $viewData['content_projects'] = $projects;
        $viewData['active']           = $status === Project::STATUS_OPEN ? 'active' : 'archived';
        $viewData['active_count']     = $countActive;
        $viewData['archived_count']   = $countArchived;

        return view('projects.index', $viewData);
    }

    /**
     * Add new project form.
     *
     * @param Form $form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getNew(Form $form)
    {
        return view('projects.new', [
            'form'     => $form,
            'projects' => $this->getLoggedUser()->projects()->get(),
        ]);
    }

    /**
     * To create a new project.
     *
     * @param Project             $project
     * @param FormRequest\Project $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNew(Project $project, FormRequest\Project $request)
    {
        $project->createProject($request->all());

        return redirect($project->to());
    }

    /**
     * Ajax: Calculate the progress of user projects.
     *
     * @param Request $request
     * @param Project $project
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postProgress(Request $request, Project $project)
    {
        // Get all projects with count of closed and opened issues
        $projects = $project->projectsWithCountIssues((array) $request->input('ids'));

        // The project progress Html and value
        $progress = $projects->transform(function (Project $project) {
            $progress = $project->getProgress();
            $view = view('partials/progress', ['text' => $progress . '%', 'progress' => $progress])->render();

            return [
                'id'       => $project->id,
                'progress' => [
                    'html'  => $view,
                    'value' => $progress,
                ],
            ];
        })->lists('progress', 'id')->all();

        return response()->json(['status' => true, 'progress' => $progress]);
    }

    /**
     * Add new issue form.
     *
     * @param IssueForm $form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getNewIssue(IssueForm $form)
    {
        return view('projects.new-issue', [
            'form'     => $form,
            'projects' => $this->getLoggedUser()->projects()->get(),
        ]);
    }

    /**
     * To create a new issue.
     *
     * @param Project\Issue           $issue
     * @param FormRequest\GlobalIssue $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNewIssue(Project\Issue $issue, FormRequest\GlobalIssue $request)
    {
        $project = Project::find((int) $request->input('project'));

        $issue->setRelation('project', $project);
        $issue->setRelation('user', $this->getLoggedUser());
        $issue->createIssue([
            'title'        => $request->input('title'),
            'body'         => $request->input('body'),
            'tag_type'     => $request->input('tag_type'),
            'upload_token' => $request->input('upload_token'),
            'assigned_to'  => (int) $project->default_assignee,
            'time_quote'   => 0,
        ]);

        return redirect($issue->to())
            ->with('notice', trans('tinyissue.issue_has_been_created'));
    }
}
