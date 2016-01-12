<?php

use Illuminate\Support\Collection;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Tag;
use Tinyissue\Services\Exporter;

class ExportProjectIssueCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function exportByKeyword(FunctionalTester $I)
    {
        $I->am('Manager User');
        $I->wantTo('Export a project issues by keyword into CSV file');

        list($manager, $project, $issues1, $issues2) = $this->_createData($I);
        $I->amLoggedAs($manager);
        $this->_exportIssues($I, $project, [
            'keyword' => 'Issue 1',
        ]);
        $this->_downloadExport($I, $project);
        $this->_assertExport($I, $project, $issues1, $issues2);
    }

    /**
     * @param FunctionalTester $I
     * @param bool             $assignIssuesToDev
     *
     * @return array
     */
    protected function _createData(FunctionalTester $I, $assignIssuesToDev = false)
    {
        $manager = $I->createUser(1, 3);
        $developer = null;
        if ($assignIssuesToDev) {
            $developer = $I->createUser(2, 2);
        }

        $project = $I->createProject(1);
        $issues1 = [
            $I->createIssue(1, $manager, $developer, $project),
            $I->createIssue(11, $manager, $developer, $project),
        ];
        $issues2 = [
            $I->createIssue(2, $manager, null, $project),
            $I->createIssue(21, $manager, null, $project),
            $I->createIssue(22, $manager, null, $project),
        ];

        return [
            $manager,
            $project,
            $issues1,
            $issues2,
            $developer,
        ];
    }

    /**
     * @param FunctionalTester $I
     * @param Project          $project
     * @param array            $params
     *
     * @return void
     */
    protected function _exportIssues(FunctionalTester $I, Project $project, array $params)
    {
        $I->amOnAction('ProjectController@getIndex', ['project' => $project]);
        $uri = $I->getApplication()->url->action('ProjectController@postExportIssues', [
            'project' => $project,
        ]);
        $I->sendAjaxPostRequest($uri, array_merge([
            '_token' => csrf_token(),
            'keyword' => '',
            'assignto' => '',
            'tags' => '',
        ], $params));
        $I->seeResponseCodeIs(200);
    }

    /**
     * @param FunctionalTester $I
     * @param Project          $project
     *
     * @return void
     */
    protected function _downloadExport(FunctionalTester $I, Project $project)
    {
        $response = $I->getJsonResponseContent();
        $file = $response->file;

        if ($response->ext !== 'csv') {
            \Excel::load(storage_path('exports/' . $response->file))->store('csv');
            unlink(storage_path('exports/' . $response->file));
            $file = str_replace('.' . $response->ext, '.csv', $response->file);
        }

        $I->amOnAction('ProjectController@getDownloadExport', [
            'project' => $project,
            'file' => $file,
        ]);
    }

    /**
     * @param FunctionalTester $I
     * @param Project          $project
     * @param array            $issues1
     * @param array            $issues2
     *
     * @return void
     */
    protected function _assertExport(FunctionalTester $I, Project $project, array $issues1, array $issues2)
    {
        $I->seeResponseCodeIs(200);
        array_walk($issues1, function ($issue) use ($I) {
            $I->see($issue->title);
        });
        $I->see($project->name);
        array_walk($issues2, function ($issue) use ($I) {
            $I->dontSee($issue->title);
        });
    }

    /**
     * @param FunctionalTester $I
     *
     * @return void
     */
    public function exportByAssignTo(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('Export a project issues by assignee into CSV file');

        list($manager, $project, $issues1, $issues2, $developer) = $this->_createData($I, true);
        $I->amLoggedAs($manager);
        $this->_exportIssues($I, $project, [
            'assignto' => $developer->id,
        ]);
        $this->_downloadExport($I, $project);
        $this->_assertExport($I, $project, $issues1, $issues2);
    }

    /**
     * @param FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function exportByTags(FunctionalTester\UserSteps $I)
    {
        $I->am('Admin User');
        $I->wantTo('Export a project issues by tag into CSV file');

        list($manager, $project, $issues1, $issues2, $developer) = $this->_createData($I, true);
        $I->login($manager->email, '123', $manager->firstname);

        // Add tag to issues1
        $I->sendAjaxGetRequest(
            $I->getApplication()->url->action('Administration\TagsController@getTags', ['term' => 'f'])
        );

        $tags = new Collection((array) $I->getJsonResponseContent());
        $tags = $tags->filter(function ($tag) use ($I) {
            return strpos(strtolower($tag->label), ':feature') !== false;
        });
        array_walk($issues1, function ($issue) use ($I, $tags) {
            foreach ($tags as $tag) {
                $issue->tags()->save(Tag::find($tag->value));
            }
        });

        $this->_exportIssues($I, $project, [
            'assignto' => $developer->id,
            'tags' => $tags->implode('value', ','),
        ]);
        $this->_downloadExport($I, $project);
        $this->_assertExport($I, $project, $issues1, $issues2);
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function exportByKeywordToXls(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('Export a project issues by keyword into Xls file');

        list($manager, $project, $issues1, $issues2) = $this->_createData($I);
        $I->amLoggedAs($manager);
        $this->_exportIssues($I, $project, [
            'keyword' => 'Issue 1',
            'format' => Exporter::TYPE_XLS,
        ]);
        $this->_downloadExport($I, $project);
        $this->_assertExport($I, $project, $issues1, $issues2);
    }
}
