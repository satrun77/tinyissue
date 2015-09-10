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

use Tinyissue\Model;

/**
 * Issue is a class to defines fields & rules for add/edit issue form
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Issue extends FormAbstract
{
    /**
     * An instance of project model
     *
     * @var Model\Project
     */
    protected $project;

    /**
     * @param array $params
     *
     * @return void
     */
    public function setup(array $params)
    {
        $this->project = $params['project'];
        if (!empty($params['issue'])) {
            $this->editingModel($params['issue']);
        }
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'submit' => $this->isEditing() ? 'update_issue' : 'create_issue',
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $issueModify = \Auth::user()->permission('issue-modify');

        // Populate tag fields with the submitted tags
        if ($this->isEditing()) {
            $selectTags = $this->getModel()->tags()->with('parent')->get()->filter(function (Model\Tag $tag) {
                return !($tag->name == Model\Tag::STATUS_OPEN || $tag->name == Model\Tag::STATUS_CLOSED);
            })->map(function (Model\Tag $tag) {
                return [
                    'value'   => $tag->id,
                    'label'   => ($tag->fullname),
                    'bgcolor' => $tag->bgcolor,
                ];
            })->toJson();
        } else {
            $selectTags = '';
        }

        $fields = [
            'title' => [
                'type'  => 'text',
                'label' => 'title',
            ],
            'body'  => [
                'type'  => 'textarea',
                'label' => 'issue',
            ],
            'tag'   => [
                'type'        => 'text',
                'label'       => 'tags',
                'multiple'    => true,
                'class'       => 'tagit',
                'data_tokens' => htmlentities($selectTags, ENT_QUOTES),
            ],
        ];

        // User with modify issue permission can assign users
        if ($issueModify) {
            $fields['assigned_to'] = [
                'type'    => 'select',
                'label'   => 'assigned_to',
                'options' => [0 => ''] + $this->project->users()->get()->lists('fullname', 'id')->all(),
                'value'   => (int) $this->project->default_assignee,
            ];
        }

        // Only on creating new issue
        if (!$this->isEditing()) {
            $fields += $this->projectUploadFields('upload', $this->project, \Auth::user());
            $fields['upload']['label'] = 'attachments';
        }

        // User with modify issue permission can add quote
        if ($issueModify) {
            $fields['time_quote'] = [
                'type'     => 'groupField',
                'label'    => 'quote',
                'fields'   => [
                    'h' => [
                        'type'          => 'number',
                        'append'        => trans('tinyissue.hours'),
                        'value'         => $this->extractQuoteValue('h'),
                        'addGroupClass' => 'col-sm-12 col-md-12 col-lg-4',
                    ],
                    'm' => [
                        'type'          => 'number',
                        'append'        => trans('tinyissue.minutes'),
                        'value'         => $this->extractQuoteValue('m'),
                        'addGroupClass' => 'col-sm-12 col-md-12 col-lg-4',
                    ],
                    's' => [
                        'type'          => 'number',
                        'append'        => trans('tinyissue.seconds'),
                        'value'         => $this->extractQuoteValue('s'),
                        'addGroupClass' => 'col-sm-12 col-md-12 col-lg-4',
                    ],
                ],
                'addClass' => 'row issue-quote',
            ];
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|max:200',
            'body'  => 'required',
        ];

        return $rules;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        if ($this->isEditing()) {
            return $this->getModel()->to('edit');
        }

        return 'project/' . $this->project->id . '/issue/new';
    }

    /**
     * Extract number of hours, or minutes, or seconds from a quote
     *
     * @param string $part
     *
     * @return float|int
     */
    protected function extractQuoteValue($part)
    {
        if ($this->getModel() instanceof Model\Project\Issue) {
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
