<?php
/*
 * This file is part of the site package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository;

use DB;
use Illuminate\Database\Eloquent\Model;
use Tinyissue\Extensions\Auth\LoggedUser;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;
use Tinyissue\Model\User\Activity as UserActivity;

abstract class RepositoryUpdater
{
    use LoggedUser;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var User
     */
    protected $user;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Proxy to model save method.
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        return $this->model->save($options);
    }

    /**
     * Proxy to model delete method.
     *
     * @return bool|null
     */
    public function delete()
    {
        return $this->model->delete();
    }

    /**
     * Proxy to model save method.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data)
    {
        $this->save($data);

        return $this->model;
    }

    /**
     * Proxy to model update method.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function update(array $attributes = [])
    {
        return $this->model->update($attributes);
    }

    /**
     * Execute method inside a db transaction.
     *
     * @param string $method
     *
     * @return mixed
     */
    protected function transaction($method)
    {
        return DB::transaction(function () use ($method) {
            return $this->$method();
        });
    }

    /**
     * Save record into activity() relation.
     *
     * @param array $input
     *
     * @return mixed
     */
    protected function saveToActivity(array $input)
    {
        return $this->model->activity()->save(new UserActivity($input));
    }

    /**
     * Save record into activities() relation.
     *
     * @param array $input
     *
     * @return mixed
     */
    protected function saveToActivities(array $input)
    {
        return $this->model->activities()->save(new UserActivity($input));
    }

    /**
     * Return the project storage disk.
     *
     * @param Project $project
     *
     * @return string
     */
    protected function getProjectStorage(Project $project)
    {
        return config('filesystems.disks.local.root') . '/' . config('tinyissue.uploads_dir') . '/' . $project->id;
    }

    /**
     * Remove project storage disk (directory).
     *
     * @param Project $project
     *
     * @return void
     */
    protected function removeProjectStorage(Project $project)
    {
        $dir = $this->getProjectStorage($project);
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }

    /**
     * Return path to an upload directory in the project storage.
     *
     * @param Project|int $projectOrId
     * @param string      $token
     *
     * @return string
     */
    protected function getUploadStorage($projectOrId, $token)
    {
        $projectId    = $projectOrId instanceof Project ? $projectOrId->id : $projectOrId;
        $relativePath = '/' . config('tinyissue.uploads_dir') . '/' . $projectId . '/' . $token;
        \Storage::disk('local')->makeDirectory($relativePath);

        return config('filesystems.disks.local.root') . $relativePath;
    }

    /**
     * Set relations from an array of values.
     *
     * @param array $relations
     *
     * @return void
     */
    protected function setModelRelations(array $relations)
    {
        foreach ($relations as $name => $object) {
            if (method_exists($this->model, $name)) {
                $this->model->setRelation($name, value($object));
            }
        }
    }

    /**
     * Set the user object of the user who is modifying the Eloquent model.
     *
     * @param User|null $user
     *
     * @return $this
     */
    public function setUser(User $user = null)
    {
        if ($user instanceof User) {
            $this->user = $user;
        }

        return $this;
    }
}
