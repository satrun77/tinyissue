<?php

namespace Tinyissue\Form;

class Note extends FormAbstract
{
    protected $project;

    public function setup($params)
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
