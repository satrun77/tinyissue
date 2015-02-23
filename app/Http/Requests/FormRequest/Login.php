<?php

namespace Tinyissue\Http\Requests\FormRequest;

class Login extends \Tinyissue\Http\Requests\Request
{
    protected $formClassName = 'Tinyissue\Form\Login';

    public function rules()
    {
        return $this->getForm()->rules();
    }

    public function authorize()
    {
        return true;
    }
}
