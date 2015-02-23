<?php

namespace Tinyissue\Http\Requests\FormRequest;

class Comment extends \Tinyissue\Http\Requests\Request
{
    protected $formClassName = 'Tinyissue\Form\Comment';

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
        return $this->getForm()->getRedirectUrl();
    }
}
