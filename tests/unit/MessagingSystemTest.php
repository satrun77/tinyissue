<?php

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Role;

/**
 * Class MessagingSystemCest
 * issues:
 * =========
 *  - add issue
 *  - update issue
 *  - assign issue
 *  - close issue
 *  - reopen issue
 *  - change tag issue
 *
 * comment:
 * =========
 * - add comment
 * - update comment
 * - delete comment
 *
 * note:
 * =========
 * - add note
 * - update note
 * - delete note
 *
 */
class MessagingSystemTest  extends \Codeception\TestCase\Test
{
    const USER_ADMIN = 4;
    const USER_MANAGER = 3;
    const USER_DEVELOPER = 2;
    const USER_USER = 1;

    protected $roles = [
        self::USER_ADMIN, // admin
        self::USER_MANAGER, // manager
        self::USER_DEVELOPER, // developer
        self::USER_DEVELOPER, // developer
        self::USER_DEVELOPER, // developer
        self::USER_USER, // user
        self::USER_USER, // user
    ];

    /**
     * @var Collection
     */
    protected $users = [];

    /**
     * @param $type
     *
     * @return Collection
     */
    protected function getUsersByType($type)
    {
        if (!array_key_exists($type, $this->users)) {
            $this->users[$type] = collect([]);
        }

        return $this->users[$type];
    }

    /**
     * @return Collection
     */
    protected function getAdmins()
    {
        return $this->getUsersByType(self::USER_ADMIN);
    }

    /**
     * @return Collection
     */
    protected function getManagers()
    {
        return $this->getUsersByType(self::USER_MANAGER);
    }

    /**
     * @return Collection
     */
    protected function getDevelopers()
    {
        return $this->getUsersByType(self::USER_DEVELOPER);
    }

    /**
     * @return Collection
     */
    protected function getUsers()
    {
        return $this->getUsersByType(self::USER_USER);
    }

    protected function createUsers()
    {
        foreach ($this->roles as $index => $role) {
            $this->getUsersByType($role)->push($this->tester->createUser($index, $role));
        }
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function testaddComment()
    {
//        $I->am('Developer User');
//        $I->wantTo('add new comment to an issue');

        $this->createUsers();

        $admin = $this->getAdmins()->random(1);
        $assignTo = $this->getDevelopers()->random(1);
        $project = $this->tester->createProject(1, collect($this->users)->collapse()->all());
        /** @var Issue $issue */
        $issue = $this->tester->createIssue(1, $admin, null, $project);

        $this->tester->seeRecord('messages_queue', [
            'event'        => Queue::ADD_ISSUE,
            'model_id'     => $issue->id,
            'model_type'   => get_class($issue),
            'change_by_id' => $admin->id,
        ]);

        $issue->reassign($assignTo, $admin);
        $this->tester->seeRecord('messages_queue', [
            'event'        => Queue::ASSIGN_ISSUE,
            'model_id'     => $issue->id,
            'model_type'   => get_class($issue),
            'change_by_id' => $admin->id,
        ]);

        return;

        // Run cron job
        \Artisan::call('schedule:run');

    }
}
