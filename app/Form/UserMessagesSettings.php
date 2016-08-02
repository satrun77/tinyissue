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
use Tinyissue\Model\Project as ProjectModel;

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
     * @var Collection
     */
    protected $messages;

    /**
     * @var Message
     */
    protected $defaultMessage;

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
        $messages = $this->getMessages()->lists('name', 'id');

        // Create field for each project
        $this->projects->each(function (ProjectModel $project) use (&$fields, $messages) {
            $messageId = $this->getSelectedMessage($project);

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

    /**
     * Returns collection of all messages options.
     *
     * @return Collection
     */
    protected function getMessages()
    {
        if (null === $this->messages) {
            $this->messages = Message::orderBy('id');
        }

        return $this->messages;
    }

    /**
     * Return default message for the current logged user based on the role.
     *
     * @return Message
     */
    protected function getDefaultMessage()
    {
        if (null === $this->defaultMessage) {
            $name                 = Message::$defaultMessageToRole[$this->getLoggedUser()->role->role];
            $this->defaultMessage = $this->getMessages()->where('name', $name)->first();
        }

        return $this->defaultMessage;
    }

    /**
     * Return value of selected message for a project.
     *
     * @param ProjectModel $project
     *
     * @return int
     */
    protected function getSelectedMessage(ProjectModel $project)
    {
        $selected = $project->projectUsers()
            ->where('user_id', '=', $this->getLoggedUser()->id)
            ->first()
            ->message_id;

        if ($selected <= 0) {
            $selected = $this->getDefaultMessage()->id;
        }

        return $selected;
    }
}
