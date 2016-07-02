<?php
/*
 * This file is part of the site package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue;

use Tinyissue\Model;
use Tinyissue\Model\Role;

trait Creatables
{
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
        $roles = [
            1 => Role::ROLE_USER,
            2 => Role::ROLE_DEVELOPER,
            3 => Role::ROLE_MANAGER,
            4 => Role::ROLE_ADMIN,
        ];

        $name = ucfirst($roles[$role]);

        $user = new Model\User([
            'email'     => $name . $index . '@user.com',
            'firstname' => 'User ' . $index,
            'lastname'  => $name,
            'password'  => \Hash::make('123'),
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
            'title'        => 'Issue ' . $index,
            'body'         => 'body of issue ' . $index,
            'time_quote'   => [
                'h'    => 0,
                'm'    => 0,
                'lock' => false,
            ],
            'upload_token' => '-',
            'tag'          => '',
            'status'       => Model\Project\Issue::STATUS_OPEN,
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

        $note = new Model\Project\Note();
        $note->setRelation('project', $project);
        $note->setRelation('createdBy', $user);
        $note->createNote([
            'note_body' => 'Note ' . $index,
        ]);

        return $note;
    }

    /**
     * Create a tag.
     *
     * @param string $name
     * @param string $parent
     * @param string $color
     * @param int    $roleLimit
     * @param int    $messageLimit
     *
     * @return Model\Tag
     */
    public function createTag($name, $parent, $color = 'red', $roleLimit = 0, $messageLimit = 0)
    {
        $parent = (new Model\Tag())->getTagByName($parent);

        $tag = (new Model\Tag())->fill([
            'name'          => $name,
            'parent_id'     => $parent->id,
            'group'         => 0,
            'bgcolor'       => $color,
            'role_limit'    => $roleLimit,
            'message_limit' => $messageLimit,
        ]);
        $tag->save();

        return $tag;
    }
}
