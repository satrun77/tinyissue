<?php

use Tinyissue\Model\Project;

class CrudProjectCest
{
    /**
     * @param FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function addProject(FunctionalTester\UserSteps $I)
    {
        $I->am('Admin User');
        $I->wantTo('add new project');

        $admin = $I->createUser(1, 4);
        $I->createUser(2, 2); // developer
        $I->createUser(3, 1); // user
        $I->login($admin->email, '123', $admin->firstname);
        $I->sendAjaxGetRequest($I->getApplication()->url->action('ProjectController@getInactiveUsers'));
        $users     = (array) $I->getJsonResponseContent();
        $userId1   = key($users);
        $userName1 = current($users);
        next($users);
        next($users);
        $userId2   = key($users);
        $userName2 = current($users);

        $I->amOnAction('ProjectsController@getNew');
        $I->fillField('name', 'project1');

        $params = [
            'user' => [
                $userId1 => $userId1,
                $userId2 => $userId2,
            ],
            'name'             => 'project1',
            'default_assignee' => $userId2,
        ];
        $I->submitForm('#submit-project', $params);
        $project = $I->fetchProjectBy('name', 'project1');
        $I->seeCurrentActionIs('ProjectController@getIndex', ['project' => $project]);
        $I->see($userName1, '//li[@id="project-user' . $userId1 . '"]');
        $I->see($userName2, '//li[@id="project-user' . $userId2 . '"]');
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function updateProject(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('edit an existing project details');

        $project = $I->createProject(1);
        $admin   = $I->createUser(1, 4);

        $I->amLoggedAs($admin);
        $I->amOnAction('ProjectController@getEdit', ['project' => $project]);
        $I->selectOption('status', Project::STATUS_ARCHIVED);
        $I->click(trans('tinyissue.update'));
        $I->amOnAction('ProjectsController@getIndex');
        $I->dontSeeLink('Project 1');
        $I->amOnAction('ProjectsController@getIndex', ['status' => Project::STATUS_ARCHIVED]);
        $I->seeLink('Project 1');
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function deleteProject(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('delete an existing project details');

        $project = $I->createProject(1);
        $admin   = $I->createUser(1, 4);

        $I->amLoggedAs($admin);
        $I->amOnAction('ProjectController@getEdit', ['project' => $project]);
        $I->click(trans('tinyissue.delete_something', ['name' => $project->name]));
        $I->seeCurrentActionIs('ProjectsController@getIndex');
        $I->dontSeeLink('Project 1');
        $I->amOnAction('ProjectsController@getIndex', ['status' => Project::STATUS_ARCHIVED]);
        $I->dontSeeLink('Project 1');
        $I->dontSeeRecord($project->getTable(), ['name' => 'Project 1']);
    }
}
