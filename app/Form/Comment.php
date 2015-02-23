<?php

namespace Tinyissue\Form;

class Comment extends FormAbstract
{
    protected $project;
    protected $issue;

    public function setup($params)
    {
        $this->project = $params['project'];
        $this->issue = $params['issue'];
    }

    public function actions()
    {
        return [
            'submit' => $this->isEditing() ? 'update' : 'comment',
        ];
    }

    public function fields()
    {
        $fields = [
            'comment' => [
                'type' => 'textarea',
                'help' => '<a href="http://daringfireball.net/projects/markdown/basics/" target="_blank">Format with Markdown</a>',
            ],
        ];

        if (!$this->isEditing()) {
            $fields['upload'] = [
                'type' => 'file',
            ];
            $fields['session'] = [
                'type'  => 'hidden',
                'value' => \Crypt::encrypt(\Auth::user()->id),
            ];
            $fields['upload_token'] = [
                'type' => 'hidden',
                'value' => md5($this->project->id.time().\Auth::user()->id.rand(1, 100)),
            ];
        }

        return $fields;
    }

    public function rules()
    {
        $rules = [
            'comment' => 'required',
        ];

        return $rules;
    }

    public function getRedirectUrl()
    {
        return $this->issue->to();
    }
}
