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
use Tinyissue\Form\FilterIssue as FilterForm;
use Tinyissue\Form\Note as NoteForm;
use Tinyissue\Form\Project as Form;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Project\Note;
use Tinyissue\Services\Exporter;

/**
 * ProjectController is the controller class for managing request related to a project.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class ProjectController extends Controller
{
    /**
     * Display project issues kanban view.
     *
     * @param Project $project
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getKanban(Project $project)
    {
        $columns = $project->getKanbanTagsForUser($this->getLoggedUser());
        $issues = $project->getIssuesGroupByTags($columns);


        return view('project.issues-kanban', [
            'sidebar' => 'project',
            'columns' => $columns,
            'issues' => $issues,
            'project' => $project,
            'open_issues_count' => $project->countOpenIssues($this->getLoggedUser()),
            'closed_issues_count' => $project->countClosedIssues($this->getLoggedUser()),
        ]);
    }

    /**
     * Display activity for a project.
     *
     * @param Project $project
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(Project $project)
    {
        $activities = $project->getRecentActivities($this->getLoggedUser());

        return $this->indexView([
            'activities'          => $activities,
            'notes_count'         => $project->countNotes(),
            'open_issues_count'   => $project->countOpenIssues($this->getLoggedUser()),
            'closed_issues_count' => $project->countClosedIssues($this->getLoggedUser()),
        ], 'activity', $project);
    }

    /**
     * Display issues for a project.
     *
     * @param FilterForm $filterForm
     * @param Request    $request
     * @param Project    $project
     * @param int        $status
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIssues(FilterForm $filterForm, Request $request, Project $project, $status = Issue::STATUS_OPEN)
    {
        if ($status === Issue::STATUS_OPEN) {
            return $this->getOpenIssues($filterForm, $request, $project);
        }

        return $this->getClosedIssues($filterForm, $request, $project);
    }

    /**
     * Display open issues.
     *
     * @param FilterForm $filterForm
     * @param Request    $request
     * @param Project    $project
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getOpenIssues(FilterForm $filterForm, Request $request, Project $project)
    {
        $issues = $project->getIssuesForLoggedUser(Issue::STATUS_OPEN, $request->all());

        return $this->indexView([
            'notes_count'         => $project->countNotes(),
            'issues'              => $issues,
            'filterForm'          => $filterForm,
            'open_issues_count'   => $issues->count(),
            'closed_issues_count' => $project->countClosedIssues($this->getLoggedUser()),
        ], 'open_issues', $project);
    }

    /**
     * Display closed issues.
     *
     * @param FilterForm $filterForm
     * @param Request    $request
     * @param Project    $project
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getClosedIssues(FilterForm $filterForm, Request $request, Project $project)
    {
        $issues = $project->getIssuesForLoggedUser(Issue::STATUS_CLOSED, $request->all());

        return $this->indexView([
            'notes_count'         => $project->countNotes(),
            'issues'              => $issues,
            'filterForm'          => $filterForm,
            'open_issues_count'   => $project->countOpenIssues($this->getLoggedUser()),
            'closed_issues_count' => $issues->count(),
        ], 'closed_issues', $project);
    }

    /**
     * Display issues assigned to current user for a project.
     *
     * @param Project $project
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAssigned(Project $project)
    {
        $issues = $project->getAssignedOrCreatedIssues($this->getLoggedUser());

        return $this->indexView([
            'notes_count'           => $project->countNotes(),
            'open_issues_count'     => $project->countOpenIssues($this->getLoggedUser()),
            'closed_issues_count'   => $project->countClosedIssues($this->getLoggedUser()),
            'assigned_issues_count' => $issues->count(),
            'issues'                => $issues,
        ], 'activity', $project);
    }

    /**
     * Display issues created to current user for a project.
     *
     * @param Project $project
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreated(Project $project)
    {
        $issues = $project->getAssignedOrCreatedIssues($this->getLoggedUser());

        return $this->indexView([
            'notes_count'           => $project->countNotes(),
            'open_issues_count'     => $project->countOpenIssues($this->getLoggedUser()),
            'closed_issues_count'   => $project->countClosedIssues($this->getLoggedUser()),
            'assigned_issues_count' => $issues->count(),
            'issues'                => $issues,
        ], 'issue_created_by_you', $project);
    }

    /**
     * Display notes for a project.
     *
     * @param Project  $project
     * @param NoteForm $form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getNotes(Project $project, NoteForm $form)
    {
        $notes = $project->getNotes();

        return $this->indexView([
            'notes_count'         => $project->countNotes(),
            'open_issues_count'   => $project->countOpenIssues($this->getLoggedUser()),
            'closed_issues_count' => $project->countClosedIssues($this->getLoggedUser()),
            'notes'               => $notes,
            'notes_count'         => $notes->count(),
            'noteForm'            => $form,
        ], 'notes', $project);
    }

    /**
     * @param mixed   $data
     * @param string  $active
     * @param Project $project
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function indexView($data, $active, Project $project)
    {
        // For logged users that is not a normal user
        if (!array_key_exists('assigned_issues_count', $data) && $this->isLoggedIn() && !$this->isLoggedNormalUser()) {
            $data['assigned_issues_count'] = $project->countAssignedIssues($this->getLoggedUser());
        } elseif ($this->isLoggedNormalUser() && $project->isPrivateInternal()) {
            $data['created_issues_count'] = $project->countCreatedIssues($this->getLoggedUser());
        }

        $data['sidebar']           = 'project';
        $data['active']            = $active;
        $data['project']           = $project;
        $data['usersCanFixIssues'] = $project->getUsersCanFixIssue();

        return view('project.index', $data);
    }

    /**
     * Edit the project.
     *
     * @param Project $project
     * @param Form    $form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getEdit(Project $project, Form $form)
    {
        return view('project.edit', [
            'form'    => $form,
            'project' => $project,
            'sidebar' => 'project',
        ]);
    }

    /**
     * To update project details.
     *
     * @param Project             $project
     * @param FormRequest\Project $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(Project $project, FormRequest\Project $request)
    {
        // Delete the project
        if ($request->has('delete-project')) {
            $project->updater()->delete();

            return redirect('projects')
                ->with('notice', trans('tinyissue.project_has_been_deleted'));
        }

        $project->updater()->update($request->all());

        return redirect($project->to())
            ->with('notice', trans('tinyissue.project_has_been_updated'));
    }

    /**
     * Ajax: returns list of users that are not in the project.
     *
     * @param Project $project
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getInactiveUsers(Project $project)
    {
        $users = $project->getNotMembers()->dropdown('fullname');

        return response()->json($users);
    }

    /**
     * Ajax: add user to the project.
     *
     * @param Project $project
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAssign(Project $project, Request $request)
    {
        $status = $project->updater($this->getLoggedUser())->assignUser((int) $request->input('user_id'));

        return response()->json(['status' => (bool) $status]);
    }

    /**
     * Ajax: remove user from the project.
     *
     * @param Project $project
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postUnassign(Project $project, Request $request)
    {
        $status = $project->updater($this->getLoggedUser())->unassignUser((int) $request->input('user_id'));

        return response()->json(['status' => (bool) $status]);
    }

    /**
     * To add a new note to the project.
     *
     * @param Project          $project
     * @param Note             $note
     * @param FormRequest\Note $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAddNote(Project $project, Note $note, FormRequest\Note $request)
    {
        $note->setRelation('project', $project);
        $note->setRelation('createdBy', $this->getLoggedUser());
        $note->updater($this->getLoggedUser())->create($request->all());

        return redirect($note->to())->with('notice', trans('tinyissue.your_note_added'));
    }

    /**
     * Ajax: To update project note.
     *
     * @param Project $project
     * @param Note    $note
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postEditNote(Project $project, Note $note, Request $request)
    {
        $body = '';
        if ($request->has('body')) {
            $note->setRelation('project', $project);
            $note->updater($this->getLoggedUser())->updateBody((string)$request->input('body'));

            $body = \Html::format($note->body);
        }

        return response()->json(['status' => true, 'text' => $body]);
    }

    /**
     * Ajax: to delete a project note.
     *
     * @param Project $project
     * @param Note    $note
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDeleteNote(Project $project, Note $note)
    {
        $note->setRelation('project', $project);
        $note->updater($this->getLoggedUser())->delete();

        return response()->json(['status' => true]);
    }

    /**
     * Ajax: generate the issues export file.
     *
     * @param Project  $project
     * @param Exporter $exporter
     * @param Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postExportIssues(Project $project, Exporter $exporter, Request $request)
    {
        // Generate export file
        $info = $exporter->exportFile(
            'Project\Issue',
            $request->input('format', Exporter::TYPE_CSV),
            $request->all()
        );

        // Download link
        $link = link_to(
            $project->to('download_export/' . $info['file']),
            trans('tinyissue.download_export'),
            ['class' => 'btn btn-link']
        );

        return response()->json([
            'link'  => $link,
            'title' => $info['title'],
            'file'  => $info['file'],
            'ext'   => $info['ext'],
        ]);
    }

    /**
     * Download and then delete an export file.
     *
     * @param Project $project
     * @param string  $file
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getDownloadExport(Project $project, $file)
    {
        $this->authorize('export', $project);

        // Filter out any characters that are not in pattern
        $file = preg_replace('/[^a-z0-9\_\.]/mi', '', $file);

        // Download export
        return response()->download(storage_path('exports/' . $file), $file)->deleteFileAfterSend(true);
    }
}
