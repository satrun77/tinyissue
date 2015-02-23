<?php

namespace Tinyissue\Http\Requests\FormRequest;

class UserSetting extends User
{
    protected $formClassName = 'Tinyissue\Form\UserSetting';

    protected function getRedirectUrl()
    {
        return 'user/settings';
    }
}
