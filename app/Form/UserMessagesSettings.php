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
use Tinyissue\Model\Message;
use Tinyissue\Model\Project;

/**
 * UserMessagesSettings is a class to defines fields & rules for edit user message settings form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class UserMessagesSettings extends FormAbstract
{
    /**
     * @var Collection
     */
    protected $projects;

    /**
     * @param Collection $projects
     *
     * @return $this
     */
    public function setProjects(Collection $projects)
    {
        $this->projects = $projects;

        return $this;
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'submit' => 'update',
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = [];

        // Change label widths
        \Former::setOption('TwitterBootstrap3.labelWidths', ['large' => 4, 'small' => 4]);

        /** @var Collection $messages Available message options */
        $messages = Message::orderBy('id')->lists('name', 'id');

        // Create field for each project
        $this->projects->each(function (Project $project) use (&$fields, $messages) {
            $messageId = $project->projectUsers->first()->message_id;
            $fields['projects[' . $project->id . ']'] = [
                'type'    => 'select',
                'label'   => $project->name,
                'options' => $messages,
                'value'   => $messageId,
                'help'    => trans('tinyissue.messages_' . strtolower($messages->get($messageId) . '_help')),
            ];
        });

        return $fields;
    }
}
