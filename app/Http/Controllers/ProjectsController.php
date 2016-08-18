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

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Tinyissue\Form\GlobalIssue as IssueForm;
use Tinyissue\Form\Project as Form;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\User;

/**
 * ProjectsController is the controller class for managing request related to projects.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class ProjectsController extends Controller
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Guard $auth, Application $app)
    {
        $this->app = $app;
        parent::__construct($auth);
    }

    /**
     * Display list of active/archived projects.
     *
     * @param int $status
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex($status = Project::STATUS_OPEN)
    {
        if ($this->isLoggedIn()) {
            $data = $this->getIndexViewDataForLoggedUser($status);
        } else {
            $data = $this->getIndexViewDataForPublicProjects($status);
        }

        return view('projects.index', $data);
    }

    /**
     * View data for logged in user.
     *
     * @param int $status
     *
     * @return array
     */
    protected function getIndexViewDataForLoggedUser($status = Project::STATUS_OPEN)
    {
        $user               = $this->getLoggedUser();
        $projects           = $user->getProjects();
        $currentTabProjects = $user->getProjectsWithOpenIssuesCount($status);
        $otherTabProjects   = $user->countProjectsByStatus($status);
        $active             = $status === Project::STATUS_OPEN ? 'active' : 'archived';
        $inactive           = $status !== Project::STATUS_OPEN ? 'active' : 'archived';

        return [
            'content_projects'   => $currentTabProjects,
            'projects'           => $projects,
            $active . '_count'   => $currentTabProjects->count(),
            $inactive . '_count' => $otherTabProjects,
            'active'             => $active,
        ];
    }

    /**
     * View data for not logged in user.
     *
     * @param int $status
     *
     * @return array
     */
    protected function getIndexViewDataForPublicProjects($status = Project::STATUS_OPEN)
    {
        $project            = $this->app->make(Project::class);
        $user               = $this->app->make(User::class);
        $activeUsers        = $user->getActiveUsers();
        $currentTabProjects = $project->getProjectsWithOpenIssuesCount($status, Project::PRIVATE_NO);
        $otherTabProjects   = $project->countProjectsByStatus($status);
        $active             = $status === Project::STATUS_OPEN ? 'active' : 'archived';
        $inactive           = $status !== Project::STATUS_OPEN ? 'active' : 'archived';

        return [
            'content_projects'   => $currentTabProjects,
            'activeUsers'        => $activeUsers,
            $active . '_count'   => $currentTabProjects->count(),
            $inactive . '_count' => $otherTabProjects,
            'sidebar'            => 'public',
            'active'             => $active,
        ];
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
            'projects' => $this->getLoggedUser()->getProjects(),
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
        $project->updater()->create($request->all());

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
        $projects = $project->getProjectsWithCountIssues((array) $request->input('ids'));

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
        })->pluck('progress', 'id')->all();

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
            'projects' => $this->getLoggedUser()->getProjects(),
        ]);
    }

    /**
     * To create a new issue.
     *
     * @param Issue                   $issue
     * @param FormRequest\GlobalIssue $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNewIssue(Issue $issue, FormRequest\GlobalIssue $request)
    {
        $issue->setRelations([
            'user' => $this->getLoggedUser(),
        ]);
        $issue->updater($this->getLoggedUser())->create([
            'project_id'   => (int) $request->input('project'),
            'title'        => $request->input('title'),
            'body'         => $request->input('body'),
            'tag_type'     => $request->input('tag_type'),
            'upload_token' => $request->input('upload_token'),
            'time_quote'   => 0,
        ]);

        return redirect($issue->to())->with('notice', trans('tinyissue.issue_has_been_created'));
    }
}
