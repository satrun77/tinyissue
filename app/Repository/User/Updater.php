<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\User;

use Hash;
use Tinyissue\Model\Project\User as ProjectUser;
use Tinyissue\Model\User;
use Tinyissue\Repository\RepositoryUpdater;

class Updater extends RepositoryUpdater
{
    /**
     * @var User
     */
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Add a new user.
     *
     * @param array $info
     *
     * @return bool
     */
    public function create(array $info)
    {
        $insert = [
            'email'     => $info['email'],
            'firstname' => $info['firstname'],
            'lastname'  => $info['lastname'],
            'role_id'   => $info['role_id'],
            'private'   => (boolean) $info['private'],
            'password'  => Hash::make($info['password']),
            'status'    => $info['status'],
            'language'  => app('tinyissue.settings')->getLanguage(),
        ];

        $this->model->fill($insert)->save();

        return $this->model;
    }

    /**
     * Soft deletes a user and empties the email.
     *
     * @return bool
     */
    public function delete()
    {
        $this->model->update([
            'email'   => $this->model->email . '_deleted',
            'deleted' => User::DELETED_USERS,
        ]);

        ProjectUser::where('user_id', '=', $this->model->id)->delete();

        return true;
    }

    /**
     * Updates the users settings.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function update(array $attributes = [])
    {
        if ($attributes['password']) {
            $attributes['password'] = Hash::make($attributes['password']);
        } elseif (empty($attributes['password'])) {
            unset($attributes['password']);
        }

        return $this->model->update($attributes);
    }

    /**
     * Update user messages setting.
     *
     * @param array $input
     */
    public function updateMessagesSettings(array $input)
    {
        return (new ProjectUser())
            ->forUser($this->model)
            ->inProjects(array_keys($input))
            ->get()
            ->each(function (ProjectUser $project) use ($input) {
                $project->message_id = $input[$project->project_id];
                $project->save();
            });
    }
}
