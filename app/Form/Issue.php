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

use Illuminate\Support\Collection;
use Tinyissue\Model;

/**
 * Issue is a class to defines fields & rules for add/edit issue form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Issue extends FormAbstract
{
    /**
     * An instance of project model.
     *
     * @var Model\Project
     */
    protected $project;

    /**
     * Collection of all tags.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $tags = null;

    /**
     * Is issue readonly.
     *
     * @var bool
     */
    protected $readOnly = false;

    /**
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    protected function getTags($type)
    {
        if ($this->tags === null) {
            $this->tags = (new Model\Tag())->getGroupTags();
        }

        return $this->tags->where('name', $type)->first()->tags;
    }

    /**
     * Returns an array of tags to be used as the selectable options.
     *
     * @param string $type
     *
     * @return array
     */
    protected function getSelectableTags($type = null)
    {
        $currentTag = $this->getIssueTag($type);

        if ($currentTag->id && (!$currentTag->canView() || $this->readOnly)) {
            $tags = [$currentTag];
        } elseif ($this->readOnly) {
            $tags = [];
        } else {
            $tags = $this->getTags($type);
        }

        return $this->generateTagRadioButtonOptions($tags, $type);
    }

    /**
     * Get issue tag for specific type/group.
     *
     * @param string $type
     *
     * @return Model\Tag
     */
    protected function getIssueTag($type)
    {
        if ($this->isEditing()) {
            $groupId     = $this->getTags($type)->first()->parent_id;
            $selectedTag = $this->getModel()->tags->where('parent_id', $groupId);

            if ($selectedTag->count() > 0) {
                return $selectedTag->last();
            }
        }

        return new Model\Tag();
    }

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
            $this->readOnly = $this->getModel()->hasReadOnlyTag($this->getLoggedUser());
        }
    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = [];

        // Check if issue is in readonly tag
        if (!$this->readOnly) {
            $actions = [
                'submit' => $this->isEditing() ? 'update_issue' : 'create_issue',
            ];

            if ($this->isEditing() && $this->getLoggedUser()->permission(Model\Permission::PERM_ISSUE_MODIFY)) {
                $actions['delete'] = [
                    'type'         => 'danger_submit',
                    'label'        => trans('tinyissue.delete_something', ['name' => '#' . $this->getModel()->id]),
                    'class'        => 'close-issue',
                    'name'         => 'delete-issue',
                    'data-message' => trans('tinyissue.delete_issue_confirm'),
                ];
            }
        }

        return $actions;
    }

    /**
     * @return array
     */
    public function fields()
    {
        $issueModify = $this->getLoggedUser()->permission('issue-modify');

        $fields = [];
        $fields += $this->readOnlyMessage();
        $fields += $this->fieldTitle();
        $fields += $this->fieldBody();
        $fields += $this->fieldTag('type');

        // Only on creating new issue
        if (!$this->isEditing()) {
            $fields += $this->fieldUpload();
        }

        // Show fields for users with issue modify permission
        if ($issueModify) {
            $fields += $this->issueModifyFields();
        }

        return $fields;
    }

    /**
     * Return a list of fields for users with issue modify permission.
     *
     * @return array
     */
    protected function issueModifyFields()
    {
        $fields = [];

        $fields['internal_status'] = [
            'type' => 'legend',
        ];

        // Status tags
        $fields += $this->fieldTag('status');

        // Assign users
        $fields += $this->fieldAssignedTo();

        // Quotes
        $fields += $this->fieldTimeQuote();

        // Resolution tags
        $fields += $this->fieldResolutionTags();

        return $fields;
    }

    /**
     * Returns message about read only issue.
     *
     * @return array
     */
    protected function readOnlyMessage()
    {
        $field = [];

        if ($this->readOnly) {
            $field = [
                'readonly' => [
                    'type'  => 'plaintext',
                    'label' => ' ',
                    'value' => '<div class="alert alert-warning">' . trans('tinyissue.readonly_issue_message') . '</div>',
                ],
            ];
        }

        return $field;
    }

    /**
     * Returns title field.
     *
     * @return array
     */
    protected function fieldTitle()
    {
        return [
            'title' => [
                'type'  => 'text',
                'label' => 'title',
            ],
        ];
    }

    /**
     * Returns body field.
     *
     * @return array
     */
    protected function fieldBody()
    {
        return [
            'body' => [
                'type'  => 'textarea',
                'label' => 'issue',
            ],
        ];
    }

    /**
     * Returns tag field.
     *
     * @param string $type
     * @param array  $prepend
     *
     * @return array
     */
    protected function fieldTag($type, array $prepend = [])
    {
        $options = $prepend + $this->getSelectableTags($type);
        $name    = 'tag_' . $type;

        return [
            $name => [
                'label'  => $type,
                'type'   => 'radioButton',
                'radios' => $options,
                'check'  => $this->getIssueTag($type)->id,
            ],
        ];
    }

    /**
     * Returns tags field.
     *
     * @return array
     */
    protected function fieldResolutionTags()
    {
        return $this->fieldTag('resolution', [
            trans('tinyissue.none') => [
                'name'      => 'tag_resolution',
                'value'     => 0,
                'data-tags' => 0,
                'color'     => '#62CFFC',
            ],
        ]);
    }

    /**
     * Returns assigned to field.
     *
     * @return array
     */
    protected function fieldAssignedTo()
    {
        return [
            'assigned_to' => [
                'type'    => 'select',
                'label'   => 'assigned_to',
                'options' => [0 => ''] + $this->project->usersCanFixIssue()->get()->lists('fullname', 'id')->all(),
                'value'   => (int) $this->project->default_assignee,
            ],
        ];
    }

    /**
     * Returns upload field.
     *
     * @return array
     */
    protected function fieldUpload()
    {
        $user                      = !$this->getLoggedUser() ? new Model\User() : $this->getLoggedUser();
        $fields                    = $this->projectUploadFields('upload', $this->project, $user);
        $fields['upload']['label'] = 'attachments';

        return $fields;
    }

    /**
     * Returns time quote field.
     *
     * @return array
     */
    protected function fieldTimeQuote()
    {
        $fields = [
            'time_quote' => [
                'type'     => 'groupField',
                'label'    => 'quote',
                'fields'   => [
                    'h'    => [
                        'type'          => 'number',
                        'append'        => trans('tinyissue.hours'),
                        'value'         => $this->extractQuoteValue('h'),
                        'addGroupClass' => 'col-sm-5 col-md-5 col-lg-4',
                    ],
                    'm'    => [
                        'type'          => 'number',
                        'append'        => trans('tinyissue.minutes'),
                        'value'         => $this->extractQuoteValue('m'),
                        'addGroupClass' => 'col-sm-5 col-md-5 col-lg-4',
                    ],
                    'lock' => [
                        'type'          => 'checkboxButton',
                        'label'         => '',
                        'noLabel'       => true,
                        'class'         => 'eee',
                        'addGroupClass' => 'sss col-sm-12 col-md-12 col-lg-4',
                        'checkboxes'    => [
                            'Lock Quote' => [
                                'value'     => 1,
                                'data-tags' => 1,
                                'color'     => 'red',
                                'checked'   => $this->isEditing() && $this->getModel()->isQuoteLocked(),
                            ],
                        ],
                        'grouped'       => true,
                    ],
                ],
                'addClass' => 'row issue-quote',
            ],
        ];

        // If user does not have access to lock quote, then remove the field
        if (!$this->getLoggedUser()->permission(Model\Permission::PERM_ISSUE_LOCK_QUOTE)) {
            unset($fields['time_quote']['fields']['lock']);

            // If quote is locked then remove quote fields
            if ($this->isEditing() && $this->getModel()->isQuoteLocked()) {
                return [];
            }
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
     * Extract number of hours, or minutes, or seconds from a quote.
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
                return ($seconds / 60) % 60;
            }
        }

        return 0;
    }

    /**
     * Returns an array structured for radio button element.
     *
     * @param Collection|array $tags
     * @param string           $type
     *
     * @return array
     */
    protected function generateTagRadioButtonOptions($tags, $type)
    {
        $options = [];

        if (is_array($tags) || $tags instanceof Collection) {
            foreach ($tags as $tag) {
                $options[ucwords($tag->name)] = [
                    'name'      => 'tag_' . $type,
                    'value'     => $tag->id,
                    'data-tags' => $tag->id,
                    'color'     => $tag->bgcolor,
                ];
            }
        }

        return $options;
    }
}
