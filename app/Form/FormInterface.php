<?php

namespace Tinyissue\Form;

use Illuminate\Database\Eloquent\Model;

interface FormInterface
{
    public function fields();

    public function rules();

    public function actions();

    public function editingModel(Model $model);

    public function isEditing();

    public function getModel();
}
