<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Form;

use Former;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Foundation\Application;
use Tinyissue\Contracts\Form\FormInterface;
use Tinyissue\Extensions\Auth\LoggedUser;
use Tinyissue\Model\Project as ProjectModel;
use Tinyissue\Model\User as UserModel;

/**
 * FormAbstract is an abstract class for Form classes.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
abstract class FormAbstract implements FormInterface
{
    use LoggedUser;

    /**
     * An instance of model .
     *
     * @var Model
     */
    protected $model;

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Set an instance of the  that own the model being edited.
     *
     * @param Model $model
     *
     * @return void|FormInterface
     */
    public function setModel(Model $model = null)
    {
        $this->model = $model;

        Former::populate($this->model);

        return $this;
    }

    /**
     * Return an instance of the  that own the model being edited.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Setup the object from the route parameters.
     *
     * @param array $params
     *
     * @return FormInterface
     */
    public function setup(array $params)
    {
        // Get the first  instance from param & set it as the owner of the form.
        $model = array_first($params, function ($value) {
            return $value instanceof Model;
        });
        $this->setModel($model);

        return $this;
    }

    /**
     * Whether or not the form is in editing of a model.
     *
     * @return bool
     */
    public function isEditing()
    {
        return $this->getModel() instanceof Model && $this->getModel()->id > 0;
    }

    /**
     * Returns form type.
     *
     * @return string
     */
    public function openType()
    {
        return 'open';
    }

    /**
     * Returns an array of form actions.
     *
     * @return array
     */
    public function actions()
    {
        return [];
    }

    /**
     * Returns an array of form fields.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    /**
     * Returns an array form rules.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Returns the form redirect url on error.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return '';
    }

    /**
     * Returns project upload fields.
     *
     * @param string       $name
     * @param ProjectModel $project
     * @param UserModel    $user
     *
     * @return array
     */
    protected function projectUploadFields($name, ProjectModel $project, UserModel $user)
    {
        return [
            $name            => [
                'type'                 => 'FileUpload',
                'data_message_success' => trans('tinyissue.success_upload'),
                'data_message_failed'  => trans('tinyissue.error_uploadfailed'),
                'multiple'             => null,
            ],
            $name . '_token' => [
                'type'  => 'hidden',
                'value' => md5($project->id . time() . $user->id . rand(1, 100)),
            ],
        ];
    }
}
