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

use Illuminate\Database\Eloquent\Model;

/**
 * FormInterface is an interface defines the structure of a Form class
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
interface FormInterface
{
    /**
     * Setup the object from the route parameters
     *
     * @param array $params
     *
     * @return void|FormInterface
     */
    public function setup(array $params);

    /**
     * Returns form type
     *
     * @return string
     */
    public function openType();

    /**
     * Returns an array of form fields
     *
     * @return array
     */
    public function fields();

    /**
     * Returns an array form rules
     *
     * @return array
     */
    public function rules();

    /**
     * Returns an array of form actions
     *
     * @return array
     */
    public function actions();

    /**
     * Set an instance of model currently being edited
     *
     * @param Model $model
     *
     * @return void|FormInterface
     */
    public function editingModel(Model $model);

    /**
     * Whether or not the form is in editing of a model
     *
     * @return boolean
     */
    public function isEditing();

    /**
     * Return an instance of the model being edited
     *
     * @return Model
     */
    public function getModel();

    /**
     * Returns the form redirect url on error
     *
     * @return string
     */
    public function getRedirectUrl();
}
