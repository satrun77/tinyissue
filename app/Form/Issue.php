<?php

namespace Tinyissue\Form;

class Issue extends FormAbstract
{
    protected $project;

    public function setup($params)
    {
        $this->project = $params['project'];
        if (!empty($params['issue'])) {
            $this->editingModel($params['issue']);
        }
    }

    public function actions()
    {
        return [
            'submit' => $this->isEditing() ? 'update_issue' : 'create_issue',
        ];
    }

    public function fields()
    {
        $fields = [
            'title' => [
                'type'  => 'text',
                'label' => 'title',
            ],
            'body'  => [
                'type'  => 'textarea',
                'label' => 'issue',
            ],
        ];

        if (\Auth::user()->permission('issue-modify')) {
            $fields['assigned_to'] = [
                'type'    => 'select',
                'label'   => 'assigned_to',
                'options' => [0 => ''] + $this->project->users()->get()->lists('fullname', 'id'),
                'value'   => (int) $this->project->default_assignee,
            ];
        }

        if (!$this->isEditing()) {
            $fields['upload'] = [
                'type' => 'file',
                'label' => 'attachments',
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
        $rules = array(
            'title' => 'required|max:200',
            'body'  => 'required',
        );

        return $rules;
    }
}
