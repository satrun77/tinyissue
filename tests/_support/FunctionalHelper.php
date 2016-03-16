<?php

namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
use Codeception\Configuration;
use Codeception\Exception\ElementNotFound;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Tinyissue\Model;

class FunctionalHelper extends \Codeception\Module
{
    /**
     * Create a user account.
     *
     * @param int $index
     * @param int $role
     *
     * @return Model\User
     */
    public function createUser($index = 0, $role = 1)
    {
        $user = new Model\User([
            'email'     => 'user' . $index . '@user.com',
            'firstname' => 'User ' . $index,
            'lastname'  => 'One',
            'password'  => Hash::make('123'),
            'role_id'   => $role,
            'language'  => 'en',
        ]);
        $user->deleted = Model\User::NOT_DELETED_USERS;
        $user->save();

        return $user;
    }

    /**
     * Create an issue with option to create a project.
     *
     * @param int           $index
     * @param Model\User    $creator
     * @param Model\User    $assign
     * @param Model\Project $project
     *
     * @return Model\Project\Issue
     */
    public function createIssue(
        $index = 0,
        Model\User $creator,
        Model\User $assign = null,
        Model\Project $project = null
    ) {
        $project = $project ?: $this->createProject($index, [$assign]);

        $issueData = [
            'title'      => 'Issue ' . $index,
            'body'       => 'body of issue ' . $index,
            'time_quote' => [
                'h' => 0,
                'm' => 0,
            ],
            'upload_token' => '-',
            'tag'          => '',
        ];
        $issueData['assigned_to'] = null !== $assign ? $assign->id : '';

        $issue = new Model\Project\Issue();
        $issue->setRelation('project', $project);
        $issue->setRelation('user', $creator);
        $issue->createIssue($issueData);

        return $issue;
    }

    /**
     * Create a project.
     *
     * @param int   $index
     * @param array $users
     *
     * @return Model\Project
     */
    public function createProject($index = 0, array $users = [])
    {
        $projectData = [
            'user' => [],
            'name' => 'Project ' . $index,
        ];
        foreach ($users as $user) {
            if (null !== $user) {
                $projectData['user'][$user->id] = $user->id;
            }
        }
        $user                            = current($users);
        $assignee                        = $user instanceof Model\User ? $user->id : '';
        $projectData['default_assignee'] = $assignee;

        $project = new Model\Project();
        $project->createProject($projectData);

        return $project;
    }

    /**
     * Create a comment in an issue.
     *
     * @param int                 $index
     * @param Model\User          $user
     * @param Model\Project\Issue $issue
     *
     * @return Model\Project\Issue\Comment
     */
    public function createComment($index = 0, Model\User $user, Model\Project\Issue $issue)
    {
        $comment = new Model\Project\Issue\Comment();
        $comment->setRelation('project', $issue->project);
        $comment->setRelation('issue', $issue);
        $comment->setRelation('user', $user);
        $comment->createComment([
            'comment'      => 'Comment ' . $index,
            'upload_token' => '-',
        ]);

        return $comment;
    }

    /**
     * Create a note in a project.
     *
     * @param int           $index
     * @param Model\User    $user
     * @param Model\Project $project
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createNote($index = 0, Model\User $user, Model\Project $project = null)
    {
        $project = $project ?: $this->createProject($index);

        $note = $project->notes()->firstOrCreate([
            'body'       => 'Note ' . $index,
            'created_by' => $user->id,
        ]);

        return $note;
    }

    /**
     * Fetch a user by column.
     *
     * @param string          $field
     * @param int|string|bool $value
     *
     * @return Model\User
     */
    public function fetchUserBy($field, $value)
    {
        return Model\User::where($field, '=', $value)->first();
    }

    /**
     * Fetch a project by column.
     *
     * @param string          $field
     * @param int|string|bool $value
     *
     * @return Model\Project
     */
    public function fetchProjectBy($field, $value)
    {
        return Model\Project::where($field, '=', $value)->first();
    }

    /**
     * Fetch an issue by column.
     *
     * @param string          $field
     * @param int|string|bool $value
     *
     * @return Model\Project\Issue
     */
    public function fetchIssueBy($field, $value)
    {
        return Model\Project\Issue::where($field, '=', $value)->first();
    }

    /**
     * Get response content as Json object.
     *
     * @return \stdClass
     */
    public function getJsonResponseContent()
    {
        return json_decode($this->getResponseContent());
    }

    /**
     * Get response content.
     *
     * @return string
     *
     * @throws \Codeception\Exception\Module
     */
    public function getResponseContent()
    {
        return $this->getModule('Laravel5')->client->getInternalResponse()->getContent();
    }

    public function sendPostRequest(
        $action,
        array $actionParams,
        array $postParams,
        array $files = [],
        array $server = [],
        $content = null
    ) {
        $module = $this->getModule('Laravel5');
        $uri    = $module->getApplication()->url->action($action, $actionParams);
        $module->client->request('POST', $uri, $postParams, $files, $server, $content);
        $this->debugResponse();
    }

    protected function debugResponse()
    {
        $module = $this->getModule('Laravel5');
        $this->debugSection('Response', $module->client->getInternalResponse()->getStatus());
        $this->debugSection('Page', $module->client->getHistory()->current()->getUri());
        $this->debugSection('Cookies', $module->client->getInternalRequest()->getCookies());
        $this->debugSection('Headers', $module->client->getInternalResponse()->getHeaders());
    }

    public function submitFormWithFileToUri($selector, $uri, array $files, array $params = [])
    {
        $form = $this->matchForm($selector);

        // Upload files
        foreach ($files as $fieldName => $fileNames) {
            $this->uploadFileWithForm($form, $uri, $fieldName, $fileNames);
        }

        // Make sure upload token is same as upload files request
        $params['upload_token'] = $form->get('upload_token')->getValue();
        $form->setValues($params);

        $this->debugSection('Uri', $form->getUri());
        $this->debugSection($form->getMethod(), $form->getValues());

        // Save Form request
        $module = $this->getModule('Laravel5');
        $module->client->request($form->getMethod(), $form->getUri(), $form->getPhpValues(), []);
        $this->debugResponse();
    }

    /**
     * @param string $selector
     *
     * @return Form
     */
    protected function matchForm($selector)
    {
        $form = $this->match($selector)->form();

        if (!$form instanceof Form) {
            throw new ElementNotFound($selector, 'Form');
        }

        return $form;
    }

    /**
     * @param string $selector
     *
     * @return Crawler
     */
    protected function match($selector)
    {
        return $this->getModule('Laravel5')->client->getCrawler()->filter($selector);
    }

    public function uploadFileWithForm($selectorOrForm, $uri, $fieldName, $fileNames)
    {
        // find form
        if (!$selectorOrForm instanceof Form) {
            $form = $this->matchForm($selectorOrForm);
        } else {
            $form = $selectorOrForm;
        }

        // Make sure fileNames is array
        if (!is_array($fileNames)) {
            $fileNames = [$fileNames];
        }

        /** @var $file \Symfony\Component\DomCrawler\Field\FileFormField */
        $file = $form->get($fieldName);

        // Upload files
        foreach ($fileNames as $fileName) {
            $filePath = Configuration::dataDir() . $fileName;
            if (!is_readable($filePath)) {
                $this->fail("file $filePath not found in Codeception data path. Only files stored in data path accepted");
            }

            // Attach file to form file
            $file->upload($filePath);

            $this->debugSection('Uri', $uri);
            $this->debugSection($form->getMethod(), $form->getValues());
            $this->debugSection('Files', $form->getPhpFiles());

            // Upload files request
            $module = $this->getModule('Laravel5');
            $module->client->request($form->getMethod(), $uri, $form->getPhpValues(), $form->getPhpFiles());
            $this->debugResponse();
        }
    }
}
