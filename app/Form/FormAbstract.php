<?php

namespace Tinyissue\Form;

use Illuminate\Database\Eloquent\Model;

abstract class FormAbstract implements FormInterface
{
    protected $model;

    public function editingModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    public function setup($params)
    {
        $model = array_first($params, function ($key, $value) {
            return $value instanceof \Illuminate\Database\Eloquent\Model;
        });
        if ($model) {
            $this->editingModel($model);
        }
        return $this;
    }

    public function isEditing()
    {
        return $this->model instanceof Model;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function openType()
    {
        return 'open';
    }
}
