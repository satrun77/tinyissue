<?php

namespace Tinyissue\Http\Controllers;

use Tinyissue\Form\Project as Form;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Illuminate\Http\Request;
use Tinyissue\Form\Note as NoteForm;
use Tinyissue\Model\Project\Note;

class ProjectController extends Controller
{
    /**
     * Display activity for a project.
     *
     * @return View
     */
    public function getIndex(Project $project)
    {
        $activities = $project->activities()
                ->with('activity', 'issue', 'user', 'assignTo', 'comment', 'note')
                ->orderBy('created_at', 'DESC')
                ->take(10)
                ->get();

        return view('project.index', [
            'project'               => $project,
            'active'                => 'activity',
            'activities'            => $activities,
            'open_issues_count'     => $project->openIssuesCount()->count(),
            'closed_issues_count'   => $project->closedIssuesCount()->count(),
            'assigned_issues_count' => $this->auth->user()->assignedIssuesCount($project->id),
            'notes_count'           => $project->notes()->count(),
            'sidebar'               => 'project'
        ]);
    }

    /**
     * Display issues for a project.
     *
     * @return View
     */
    public function getIssues(Project $project, $status = Issue::STATUS_OPEN)
    {
        $active = $status == Issue::STATUS_OPEN ? 'open_issue' : 'closed_issue';
        $issues = $project->listIssues($status);
        if ($status == Issue::STATUS_OPEN) {
            $closedIssuesCount = $project->closedIssuesCount()->count();
            $openIssuesCount = $issues->count();
        } else {
            $closedIssuesCount = $issues->count();
            $openIssuesCount = $project->openIssuesCount()->count();
        }

        return view('project.index', [
            'project'               => $project,
            'active'                => $active,
            'issues'                => $issues,
            'open_issues_count'     => $openIssuesCount,
            'closed_issues_count'   => $closedIssuesCount,
            'assigned_issues_count' => $this->auth->user()->assignedIssuesCount($project->id),
            'notes_count'           => $project->notes()->count(),
            'sidebar'               => 'project'
        ]);
    }

    /**
     * Display issues assigned to current user for a project.
     *
     * @return View
     */
    public function getAssigned(Project $project)
    {
        $issues = $project->listAssignedIssues($this->auth->user()->id);

        return view('project.index', [
            'project'               => $project,
            'active'                => 'issue_assigned_to_you',
            'issues'                => $issues,
            'open_issues_count'     => $project->openIssuesCount()->count(),
            'closed_issues_count'   => $project->closedIssuesCount()->count(),
            'assigned_issues_count' => $issues->count(),
            'notes_count'           => $project->notes()->count(),
            'sidebar'               => 'project'
        ]);
    }

    /**
     * Display notes for a project
     *
     * @param Project $project
     * @return \Illuminate\View\View
     */
    public function getNotes(Project $project, NoteForm $form)
    {
        $notes = $project->notes()->with('createdBy')->get();

        return view('project.index', [
            'project'               => $project,
            'active'                => 'notes',
            'notes'                 => $notes,
            'open_issues_count'     => $project->openIssuesCount()->count(),
            'closed_issues_count'   => $project->closedIssuesCount()->count(),
            'assigned_issues_count' => $this->auth->user()->assignedIssuesCount($project->id),
            'notes_count'           => $notes->count(),
            'sidebar'               => 'project',
            'noteForm'              => $form,
        ]);
    }
    /**
     * Edit the project.
     *
     * @return View
     */
    public function getEdit(Project $project, Form $form)
    {
        return view('project.edit', [
            'form'    => $form,
            'project' => $project,
            'sidebar' => 'project'
        ]);
    }

    public function postEdit(Project $project, FormRequest\Project $request)
    {
        // Delete the project
        if ($request->has('delete-project')) {
            $project->delete();

            return redirect('projects')
                            ->with('notice', trans('tinyissue.project_has_been_deleted'));
        }

        $project->update($request->all());

        return redirect($project->to('edit'))
                        ->with('notice', trans('tinyissue.project_has_been_updated'));
    }

    public function getInactiveUsers(Project $project)
    {
        $users = $project->usersNotIn();

        return response()->json($users);
    }

    public function postAssign(Project $project, Request $request)
    {
        $status = false;
        if ($request->has('user_id')) {
            $project->assignUser($request->input('user_id'));
            $status = true;
        }

        return response()->json(['status' => $status]);
    }

    public function postUnassign(Project $project, Request $request)
    {
        $status = false;
        if ($request->has('user_id')) {
            $project->unassignUser($request->input('user_id'));
            $status = true;
        }

        return response()->json(['status' => $status]);
    }

    public function postAddNote(Project $project, Note $note, FormRequest\Note $request)
    {
        $note->setRelation('project', $project);
        $note->setRelation('createdBy', $this->auth->user());
        $note->createNote($request->all());

        return redirect($note->to())->with('notice', trans('tinyissue.your_note_added'));
    }

    public function postEditNote(Project $project, Project\Note $note, Request $request)
    {
        $body = '';
        if ($request->has('body')) {
            $note->setRelation('project', $project);
            $note->body = $request->input('body');
            $note->save();
            $body = \Html::format($note->body);
        }

        return response()->json(['status' => true, 'text' => $body]);
    }

    public function getDeleteNote(Project $project, Project\Note $note)
    {
        $note->delete();

        return response()->json(['status' => true]);
    }
}
