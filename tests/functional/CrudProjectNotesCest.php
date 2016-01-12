<?php

use Tinyissue\Model\Project;

class CrudProjectNotesCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function viewNotes(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('view existing notes in a project');

        $user1 = $I->createUser(1, 1);
        $developer1 = $I->createUser(2, 2);
        $I->amLoggedAs($developer1);

        $project = $I->createProject(1);
        $note1 = $I->createNote(1, $user1, $project);
        $note2 = $I->createNote(2, $developer1, $project);

        $I->amOnAction('ProjectController@getNotes', ['project' => $project]);
        $I->see('Note 1', '//li[@id="note' . $note1->id . '"]');
        $I->see('Note 2', '//li[@id="note' . $note2->id . '"]');
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function addNote(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('add new note to a project');

        $admin = $I->createUser(1, 4);
        $I->amLoggedAs($admin);

        $project = $I->createProject(1);

        $I->amOnAction('ProjectController@getNotes', ['project' => $project]);
        $I->submitForm('#new-note form', [
            'note_body' => 'note one',
        ]);
        $I->amOnAction('ProjectController@getNotes', ['project' => $project]);
        $I->see('note one', '//li[@id="note' . $project->notes()->first()->id . '"]');
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function updateNote(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('edit an existing note in the project');

        $admin = $I->createUser(1, 4);
        $I->amLoggedAs($admin);

        $note = $I->createNote(1, $admin);
        $project = $note->project;

        $I->amOnAction('ProjectController@getNotes', ['project' => $project]);
        $I->see('Note 1', '//li[@id="note' . $note->id . '"]');

        $uri = $I->getApplication()->url->action('ProjectController@postEditNote', ['project' => $project, 'note' => $note]);
        $I->sendAjaxPostRequest($uri, [
            'body' => 'note one updated',
            '_token' => csrf_token(),
        ]);
        $I->seeResponseCodeIs(200);
        $I->amOnAction('ProjectController@getNotes', ['project' => $project]);
        $I->see('note one updated', '//li[@id="note' . $note->id . '"]');
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function deleteNote(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('delete an existing note from a project');

        $admin = $I->createUser(1, 4);
        $I->amLoggedAs($admin);

        $project = $I->createProject(1);
        $note1 = $I->createNote(1, $admin, $project);
        $note2 = $I->createNote(2, $admin, $project);

        $I->amOnAction('ProjectController@getNotes', ['project' => $project]);
        $I->see('Note 1', '//li[@id="note' . $note1->id . '"]');
        $I->see('Note 2', '//li[@id="note' . $note2->id . '"]');
        $uri = $I->getApplication()->url->action('ProjectController@getDeleteNote', ['project' => $project, 'note' => $note2]);
        $I->sendAjaxGetRequest($uri);
        $I->seeResponseCodeIs(200);
        $I->amOnAction('ProjectController@getNotes', ['project' => $project]);
        $I->see('Note 1', '//li[@id="note' . $note1->id . '"]');
        $I->dontSee('Note 2', '//li[@id="note' . $note2->id . '"]');
    }
}
