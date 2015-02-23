<?php

namespace Tinyissue\Http\Requests\FormRequest;

class User extends \Tinyissue\Http\Requests\Request
{
    protected $formClassName = 'Tinyissue\Form\User';

    public function rules()
    {
        return $this->getForm()->rules();
    }

    public function authorize()
    {
        // Only allow logged in users
        return \Auth::check();
    }

    public function response(array $errors)
    {
        return parent::response($errors)->with('notice-error', trans('tinyissue.we_have_some_errors'));
    }

    protected function getRedirectUrl()
    {
        if ($this->getForm()->isEditing()) {
            return 'administration/users/edit/'.$this->getForm()->getModel()->id;
        }

        return 'administration/users/add/';
    }
}
