<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Repository\Repository;
use Tinyissue\Repository\RepositoryUpdater;

/**
 * Class ModelAbstract
 *
 * @method \Illuminate\Support\Collection get()
 * @method int count()
 * @method \Illuminate\Support\Collection pluck($name, $key)
 * @method static orderBy($column, $direction = 'asc')
 * @method static select($columns = ['*'])
 * @method static where($key, $operator, $value = null)
 * @method static dropdown($text = 'name', $value = 'id')
 * @method static find($id, $columns = ['*'])
 * @method static whereIn($column, $values, $boolean = 'and', $not = false)
 */
abstract class ModelAbstract extends Model
{
    /**
     * @var RepositoryUpdater
     */
    protected $updater;

    /**
     * @var Repository
     */
    protected $counter;

    /**
     * @var Repository
     */
    protected $fetcher;

    /**
     * Factory method to instantiate a repository class based on a type.
     *
     * @param string $type
     *
     * @return Repository|RepositoryUpdater
     */
    protected function factoryRepository($type)
    {
        if (is_null($this->$type)) {
            $class = $this->getRepositoryClassName($type);
            if (class_exists($class)) {
                $this->$type = new $class($this);
                if (!$this->$type instanceof RepositoryUpdater && !$this->$type instanceof Repository) {
                    throw new \DomainException(sprintf('Unable to find the repository class "%s"', $class));
                }
            }
        }

        return $this->$type;
    }

    /**
     * Construct the repository class name for current model.
     *
     * @param string $type
     *
     * @return string
     */
    protected function getRepositoryClassName($type)
    {
        return str_replace(__NAMESPACE__, 'Tinyissue\\Repository', static::class) . '\\' . ucfirst($type);
    }

    /**
     * Get repository for updating / modifying data.
     *
     * @param User|null $user
     *
     * @return RepositoryUpdater
     */
    public function updater(User $user = null)
    {
        $this->factoryRepository('updater');

        if ($this->updater instanceof RepositoryUpdater) {
            $this->updater->setUser($user);
        }

        return $this->updater;
    }

    /**
     * get repository class for counting records.
     *
     * @return Repository
     */
    public function counter()
    {
        $this->factoryRepository('counter');

        return $this->counter;
    }

    /**
     * Get repository class for fetching data.
     *
     * @return Repository
     */
    public function fetcher()
    {
        $this->factoryRepository('fetcher');

        return $this->fetcher;
    }

    /**
     * Attempt to proxy the method call to other related classes if possible.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $collection = collect(['counter', 'fetcher', 'updater']);

        $caller = $collection->first(function ($type) use ($name) {
            $related = $this->$type();

            return !is_null($related) && method_exists($this->$type, $name);
        });

        if ($caller) {
            return $this->{$caller}->{$name}(...$arguments);
        }

        return parent::__call($name, $arguments);
    }

    /**
     * Return instance of the current class.
     *
     * @param array $attributes
     *
     * @return static
     */
    public static function instance(array $attributes = [])
    {
        $model = new static($attributes);
        $model->exists = false;

        return $model;
    }

    /**
     * Returns the aggregate value of a field.
     *
     * @param string $field
     *
     * @return int
     */
    protected function getCountAttribute($field)
    {
        // if relation is not loaded already, let's do it first
        if (!array_key_exists($field, $this->relations)) {
            $this->load($field);
        }

        $related = $this->getRelation($field);

        // then return the count directly
        return ($related) ? (int)$related->aggregate : 0;
    }
}
