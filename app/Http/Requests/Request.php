<?php

namespace Tinyissue\Http\Requests;

use Tinyissue\Form\FormInterface;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    protected $form;
    protected $formClassName;

    public function setForm(FormInterface $form)
    {
        $this->form = $form;

        return $this;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function getFormClassName()
    {
        return $this->formClassName;
    }
}
