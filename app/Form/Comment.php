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
 * Comment is a class to defines fields & rules for add/edit comments form
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Comment extends FormAbstract
{
    /**
     * An instance of project model
     *
     * @var \Tinyissue\Model\Project
     */
    protected $project;
    /**
     * An instance of project issue model
     *
     * @var \Tinyissue\Model\Project\issue
     */
    protected $issue;

    public function setup(array $params)
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

        // Only for adding new comment
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
