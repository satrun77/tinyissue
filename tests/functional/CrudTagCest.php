<?php

use Tinyissue\Model\Tag;

class CrudTagCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function addTag(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('add new tag');

        $tag   = new Tag();
        $group = $tag->getGroups()->random(1);
        $data  = [
            'name'       => 'tag1',
            'parent_id'  => $group->id,
            'group'      => 0,
            'bgcolor'    => 'red',
            'role_limit' => '',
        ];

        $I->amLoggedAs($I->createUser(1, 4));
        $I->amOnAction('Administration\TagsController@getNew');
        $I->submitForm('form', $data);
        $I->seeCurrentActionIs('Administration\\TagsController@getIndex');
        $I->seeRecord($tag->getTable(), $data);
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function updateTag(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('edit an existing tag');

        $tag          = (new Tag())->where('group', '=', false)->get()->random(1);
        $data         = $tag->toArray();
        $data['name'] = 'tag updated';
        $tagName      = $tag->name;

        $I->amLoggedAs($I->createUser(1, 4));
        $I->amOnAction('Administration\TagsController@getIndex');
        $I->click($this->_editTagSelector($tagName));
        $I->seeCurrentActionIs('Administration\TagsController@getEdit', ['tag' => $tag]);
        $I->submitForm('form', $data);
        $I->amOnAction('Administration\\TagsController@getIndex');
        $I->see($data['name'], $this->_editTagSelector($data['name']));
        $I->dontSee($data['name'], $this->_editTagSelector($tagName));
    }

    /**
     * Generate xpath query to tag link.
     *
     * @param string $tagName
     *
     * @return string
     */
    protected function _editTagSelector($tagName)
    {
        return '//li[contains(concat(" ", normalize-space(@class), " "), " list-group-item ") and contains(translate(., "' . strtoupper($tagName) . '", "' . $tagName . '"), "' . $tagName . '")]/a[last()]';
    }
}
