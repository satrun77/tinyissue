<?php

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Role;
use Tinyissue\Model\User;

class MessagingSystemAbstract extends \Codeception\TestCase\Test
{
    const USER_ADMIN     = 4;
    const USER_MANAGER   = 3;
    const USER_DEVELOPER = 2;
    const USER_USER      = 1;

    protected $roles = [
        self::USER_ADMIN,
        self::USER_MANAGER,
        self::USER_DEVELOPER,
        self::USER_DEVELOPER,
        self::USER_DEVELOPER,
        self::USER_USER,
        self::USER_USER,
    ];

    /**
     * @var Collection
     */
    protected $users = [];

    protected $regExp = [
        'tag'        => '/<th\b[^>]*>(\s+){label}:(\s+)<\/th>(\s+)<td\b[^>]*>(\s+)<span\b[^>]*>{now}<\/span>(\s+)<\/td>/ims',
        'tag_change' => '/<th\b[^>]*>(\s+){label}:(\s+)<\/th>(\s+)<td\b[^>]*>(\s+)<span\b[^>]*>{was}<\/span>(\s+)<span\b[^>]*>{now}<\/span>(\s+)<\/td>/ims',
    ];

    /**
     * Migrates the database and set the mailer to 'pretend'.
     * This will cause the tests to run quickly.
     */
    protected $callbackAssert;

    protected static $mailer = null;

    protected function _before()
    {
        $this->mockMailer();
    }

    protected function _after()
    {
        $this->sendMessagesAndAssert();
        Issue::flushEventListeners();
        Issue\Comment::flushEventListeners();
        Project\Note::flushEventListeners();

        \Mockery::close();
    }

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
        return $this->getUsersByType(self::USER_ADMIN)->reverse();
    }

    /**
     * @return Collection
     */
    protected function getManagers()
    {
        return $this->getUsersByType(self::USER_MANAGER)->reverse();
    }

    /**
     * @return Collection
     */
    protected function getDevelopers()
    {
        return $this->getUsersByType(self::USER_DEVELOPER)->reverse();
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

    protected function getRegExp($name, array $replaces)
    {
        $pattern = $this->regExp[$name];

        return strtr($pattern, $replaces);
    }

    protected function mockMailer()
    {
        if (null !== self::$mailer) {
            return self::$mailer;
        }

        self::$mailer = app('mailer')->getSwiftMailer();
        self::$mailer = \Mockery::mock(self::$mailer, function ($mock) {
            $mock->shouldReceive('send')
                ->withArgs([
                    \Mockery::on(function (\Swift_Message $message) {
                        if (method_exists($this, $this->callbackAssert['callback'])) {
                            $parameters = $this->callbackAssert['parameters'];
                            array_unshift($parameters, $message);
                            call_user_func_array(
                                [$this, $this->callbackAssert['callback']],
                                $parameters
                            );
                        }

                        return true;
                    }),
                    \Mockery::any(),
                ]);
        })->byDefault();
        app('mailer')->setSwiftMailer(self::$mailer);

        return self::$mailer;
    }

    protected function sendMessagesAndAssert($callback = '', array $parameters = [])
    {
        $this->callbackAssert = [
            'parameters' => $parameters,
            'callback'   => $callback,
        ];

        // Run cron job
        \Artisan::call('tinyissue:messages');
    }

    protected function seeRecordInQueue($event, $model, $changeBy)
    {
        $this->tester->seeRecord('messages_queue', [
            'event'        => $event,
            'model_id'     => $model->id,
            'model_type'   => get_class($model),
            'change_by_id' => $changeBy->id,
        ]);
    }
}
