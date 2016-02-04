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
 * Comment is a class to defines fields & rules for add/edit comments form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Comment extends FormAbstract
{
    /**
     * An instance of project model.
     *
     * @var \Tinyissue\Model\Project
     */
    protected $project;
    /**
     * An instance of project issue model.
     *
     * @var \Tinyissue\Model\Project\issue
     */
    protected $issue;

    /**
     * @param array $params
     *
     * @return void
     */
    public function setup(array $params)
    {
        $this->project = $params['project'];
        $this->issue   = $params['issue'];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'submit' => $this->isEditing() ? 'update' : 'comment',
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = [
            'comment' => [
                'type' => 'textarea',
                'help' => '<a href="http://daringfireball.net/projects/markdown/basics/" target="_blank">Format with Markdown</a>',
            ],
        ];

        // Only for adding new comment
        if (!$this->isEditing()) {
            $fields += $this->projectUploadFields('upload', $this->project, \Auth::user());
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            'comment' => 'required',
        ];

        return $rules;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->issue->to();
    }
}
