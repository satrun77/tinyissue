<?php

namespace Tinyissue\Form;

use Tinyissue\Model\Project;

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
        $issueModify = \Auth::user()->permission('issue-modify');
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

        if ($issueModify) {
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

        if ($issueModify) {
            $fields['time_quote'] = [
                'type'     => 'groupText',
                'label'    => 'quote',
                'fields'   => [
                    'h' => [
                        'type'   => 'number',
                        'append' => trans('tinyissue.hours'),
                        'value'  => $this->extractQuoteValue('h'),
                    ],
                    'm' => [
                        'type'   => 'number',
                        'append' => trans('tinyissue.minutes'),
                        'value'  => $this->extractQuoteValue('m'),
                    ],
                    's' => [
                        'type'   => 'number',
                        'append' => trans('tinyissue.seconds'),
                        'value'  => $this->extractQuoteValue('s'),
                    ],
                ],
                'addClass' => 'issue-quote'
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

    public function getRedirectUrl()
    {
        if ($this->isEditing()) {
            return $this->getModel()->to('edit');
        }

        return 'project/' . $this->project->id . '/issue/new';
    }

    protected function extractQuoteValue($part)
    {
        if ($this->getModel() instanceof Project\Issue) {
            $seconds = $this->getModel()->time_quote;
            if ($part === 'h') {
                return floor($seconds / 3600);
            }

            if ($part === 'm') {
                return (($seconds / 60) % 60);
            }

            if ($part === 's') {
                return $seconds % 60;
            }
        }

        return 0;
    }
}
