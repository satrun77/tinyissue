<?php
/*
 * This file is part of the site package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tinyissue\Model;

trait TestTrait {

    /**
     * Create a user account.
     *
     * @param int $index
     * @param int $role
     *
     * @return Model\User
     */
    public function createUser($index = 0, $role = 1)
    {
        $user = new Model\User([
            'email'     => 'user' . $index . '@user.com',
            'firstname' => 'User ' . $index,
            'lastname'  => 'One',
            'password'  => Hash::make('123'),
            'role_id'   => $role,
            'language'  => 'en',
        ]);
        $user->deleted = Model\User::NOT_DELETED_USERS;
        $user->save();

        return $user;
    }

    /**
     * Create an issue with option to create a project.
     *
     * @param int           $index
     * @param Model\User    $creator
     * @param Model\User    $assign
     * @param Model\Project $project
     *
     * @return Model\Project\Issue
     */
    public function createIssue(
        $index = 0,
        Model\User $creator,
        Model\User $assign = null,
        Model\Project $project = null
    ) {
        $project = $project ?: $this->createProject($index, [$assign]);

        $issueData = [
            'title'      => 'Issue ' . $index,
            'body'       => 'body of issue ' . $index,
            'time_quote' => [
                'h' => 0,
                'm' => 0,
            ],
            'upload_token' => '-',
            'tag'          => '',
        ];
        $issueData['assigned_to'] = null !== $assign ? $assign->id : '';

        $issue = new Model\Project\Issue();
        $issue->setRelation('project', $project);
        $issue->setRelation('user', $creator);
        $issue->createIssue($issueData);

        return $issue;
    }

    /**
     * Create a project.
     *
     * @param int   $index
     * @param array $users
     *
     * @return Model\Project
     */
    public function createProject($index = 0, array $users = [])
    {
        $projectData = [
            'user' => [],
            'name' => 'Project ' . $index,
        ];
        foreach ($users as $user) {
            if (null !== $user) {
                $projectData['user'][$user->id] = $user->id;
            }
        }
        $user                            = current($users);
        $assignee                        = $user instanceof Model\User ? $user->id : '';
        $projectData['default_assignee'] = $assignee;

        $project = new Model\Project();
        $project->createProject($projectData);

        return $project;
    }

    /**
     * Create a comment in an issue.
     *
     * @param int                 $index
     * @param Model\User          $user
     * @param Model\Project\Issue $issue
     *
     * @return Model\Project\Issue\Comment
     */
    public function createComment($index = 0, Model\User $user, Model\Project\Issue $issue)
    {
        $comment = new Model\Project\Issue\Comment();
        $comment->setRelation('project', $issue->project);
        $comment->setRelation('issue', $issue);
        $comment->setRelation('user', $user);
        $comment->createComment([
            'comment'      => 'Comment ' . $index,
            'upload_token' => '-',
        ]);

        return $comment;
    }

    /**
     * Create a note in a project.
     *
     * @param int           $index
     * @param Model\User    $user
     * @param Model\Project $project
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createNote($index = 0, Model\User $user, Model\Project $project = null)
    {
        $project = $project ?: $this->createProject($index);

        $note = $project->notes()->firstOrCreate([
            'body'       => 'Note ' . $index,
            'created_by' => $user->id,
        ]);

        return $note;
    }

}