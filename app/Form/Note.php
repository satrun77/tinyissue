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

/**
 * Note is a class to defines fields & rules for add/edit note form
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Note extends FormAbstract
{
    /**
     * An instance of project model
     *
     * @var Project
     */
    protected $project;

    public function setup(array $params)
    {
        $this->project = $params['project'];
    }

    public function actions()
    {
        return [
            'submit' => $this->isEditing() ? 'update' : 'save',
        ];
    }

    public function fields()
    {
        $fields = [
            'note_body' => [
                'type' => 'textarea',
                'help' => '<a href="http://daringfireball.net/projects/markdown/basics/" target="_blank">Format with Markdown</a>',
            ],
        ];

        return $fields;
    }

    public function rules()
    {
        $rules = [
            'note_body' => 'required',
        ];

        return $rules;
    }

    public function getRedirectUrl()
    {
        return $this->project->to('notes');
    }
}
