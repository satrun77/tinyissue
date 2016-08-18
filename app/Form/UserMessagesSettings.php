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
        $messages = $this->getMessages()->dropdown();

        // Create field for each project
        $this->projects->each(function (ProjectModel $project) use (&$fields, $messages) {
            $fields['projects[' . $project->id . ']'] = $this->getSelectField($project, $messages);
        });

        return $fields;
    }

    /**
     * @param ProjectModel $project
     * @param array        $messages
     *
     * @return array
     */
    protected function getSelectField(ProjectModel $project, array $messages)
    {
        $messageId = $this->getSelectedMessage($project);

        return [
            'type'    => 'select',
            'label'   => $project->name,
            'options' => $messages,
            'value'   => $messageId,
            'help'    => trans('tinyissue.messages_' . strtolower($messages[$messageId] . '_help')),
        ];
    }

    /**
     * Returns collection of all messages options.
     *
     * @return Collection
     */
    protected function getMessages()
    {
        if (null === $this->messages) {
            $this->messages = $this->app->make(Message::class)->all();
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
            $name = Message::$defaultMessageToRole[$this->getLoggedUser()->getRoleName()];
            $this->defaultMessage = $this->getMessages()->getByName($name);
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
        $selected = $project->getPreferredMessageIdForUser($this->getLoggedUser()->id);

        if ($selected <= 0) {
            $selected = $this->getDefaultMessage()->id;
        }

        return $selected;
    }
}
