<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Requests;

use Tinyissue\Form\FormInterface;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request is an abstract class for Form Request classes.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
abstract class Request extends FormRequest
{
    /**
     * An instance of Form.
     *
     * @var FormInterface
     */
    protected $form;

    /**
     * Class name of the form.
     *
     * @var string
     */
    protected $formClassName;

    /**
     * Set the instance of the form.
     *
     * @param FormInterface $form
     *
     * @return $this
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Returns an instance of the form.
     *
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Returns the class name of the form.
     *
     * @return string
     */
    public function getFormClassName()
    {
        return $this->formClassName;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return $this->getForm()->rules();
    }

    /**
     * @return bool
     */
    public function authorize()
    {
        // Only allow logged in users
        return \Auth::check();
    }

    /**
     * @param array $errors
     *
     * @return mixed
     */
    public function response(array $errors)
    {
        return parent::response($errors)->with('notice-error', trans('tinyissue.we_have_some_errors'));
    }

    /**
     * @return string
     */
    protected function getRedirectUrl()
    {
        return $this->getForm()->getRedirectUrl();
    }
}
