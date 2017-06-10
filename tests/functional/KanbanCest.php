<?php

use Tinyissue\Model\Tag;

class KanbanCest
{
    /**
     * @param FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function viewKanbanAndMoveIssue(FunctionalTester\UserSteps $I)
    {
        $I->am('Developer User');
        $I->wantTo('view my Kanban page of my issues & change status of an issue');

        $admin     = $I->createUser(1, 4);
        $developer = $I->createUser(2, 2);

        $statuses = [
            'open'     => $I->createTag('Open', Tag::GROUP_STATUS, 'red'),
            'progress' => $I->createTag('In Progress', Tag::GROUP_STATUS, 'red'),
        ];
        $project = $I->createProject(1, [$developer, $admin]);
        $issue   = $I->createIssue(1, $admin, $developer, $project);
        $issue->setRelation('project', $project);
        $issue->setRelation('updatedBy', $admin);
        $issue->updater()->update([
            'title'       => $issue->title,
            'body'        => $issue->body,
            'assigned_to' => $issue->assigned_to,
            'time_quote'  => $issue->time_quote,
            'tag_status'  => $statuses['open']->id,
        ]);

        $I->amLoggedAs($admin);
        $I->amOnAction('ProjectController@getEdit', ['project' => $project]);
        $I->checkOption('//input[@value="' . $statuses['open']->id . '"]');
        $I->checkOption('//input[@value="' . $statuses['progress']->id . '"]');
        $I->click(trans('tinyissue.update'));
        $I->click(trans('tinyissue.logout'), 'a');
        $I->dontSeeAuthentication();
        $I->amOnAction('HomeController@getIndex');
        $I->login($developer->email, '123');
        $xpathProjectLink = '//nav[@id="kanban-projects-nav"]//a[@data-project-id="' . $project->id . '"]';
        $I->amOnAction('HomeController@getDashboard');
        $I->amOnAction('UserController@getKanbanIssues');
        $I->see($project->name, $xpathProjectLink);
        $I->click($project->name, $xpathProjectLink);
        $I->seeCurrentActionIs('UserController@getKanbanIssues', ['project' => $project]);

        foreach ($statuses as $status) {
            $statusClassName = 'column-' . $status->id;
            $I->seeElement('//li[contains(@class, \'' . $statusClassName . '\')]');
        }

        $statusClassName = 'column-' . $statuses['open']->id;
        $issueClassName  = 'issue-' . $issue->id;
        $I->seeElement('//li[contains(@class, \'' . $statusClassName . '\')]//div[contains(@class, \'' . $issueClassName . '\')]');
        $I->see($issue->name, '//li[contains(@class, \'' . $statusClassName . '\')]//div[contains(@class, \'' . $issueClassName . '\')]//div[@class="summary"]/span');

        $uri = $I->getApplication()->url->action('Project\IssueController@postChangeKanbanTag', ['issue' => $issue]);
        $I->sendAjaxPostRequest($uri, [
            'oldtag' => $statuses['open']->id,
            'newtag' => $statuses['progress']->id,
            '_token' => csrf_token(),
        ]);
        $I->seeResponseCodeIs(200);
        $I->amOnAction('UserController@getKanbanIssues', ['project' => $project]);

        $statusClassName = 'column-' . $statuses['progress']->id;
        $I->seeElement('//li[contains(@class, \'' . $statusClassName . '\')]//div[contains(@class, \'' . $issueClassName . '\')]');
        $I->see($issue->name, '//li[contains(@class, \'' . $statusClassName . '\')]//div[contains(@class, \'' . $issueClassName . '\')]//div[@class="summary"]/span');
    }
}
