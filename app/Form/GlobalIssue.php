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
 * GlobalIssue is a class to defines fields & rules for adding an issue form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class GlobalIssue extends Issue
{
    /**
     * List of projects.
     *
     * @var Collection
     */
    protected $projects;

    /**
     * Returns list of logged in user projects.
     *
     * @return Collection
     */
    protected function getProjects()
    {
        if (is_null($this->projects)) {
            $this->projects = $this->getLoggedUser()->getProjects();
        }

        return $this->projects;
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public function setup(array $params)
    {
        $this->project = $this->app->make(Model\Project::class);
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'submit' => 'create_issue',
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = $this->fieldTitle();

        $fields['project'] = [
            'type'    => 'select',
            'label'   => 'project',
            'options' => $this->getProjects()->dropdown(),
        ];

        $fields += $this->fieldBody();

        $fields += $this->fieldTag('type');

        // Only on creating new issue
        $fields += $this->fieldUpload();

        return $fields;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules            = parent::rules();
        $rules['project'] = 'required|in:' . $this->getProjects()->implode('id', ',');

        return $rules;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return 'projects/new_issue';
    }
}
