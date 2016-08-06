<?php

use Tinyissue\Model\User;

class UserModelTest extends \Codeception\TestCase\Test
{
    const USER_ADMIN     = 4;
    const USER_MANAGER   = 3;
    const USER_DEVELOPER = 2;
    const USER_USER      = 1;

    public function testAnonymousName()
    {
        $manager   = $this->tester->createUser(2, self::USER_MANAGER);
        $developer = $this->tester->createUser(3, self::USER_DEVELOPER);
        $admin     = $this->tester->createUser(4, self::USER_ADMIN);

        $this->assertNotEquals($developer->fullname, trans('tinyissue.anonymous'));
        $developer->private = User::PRIVATE_YES;
        $developer->save();
        $this->assertEquals($developer->fullname, trans('tinyissue.anonymous'));

        auth()->login($manager);
        $this->assertNotEquals($developer->fullname, trans('tinyissue.anonymous'));
        auth()->login($admin);
        $this->assertNotEquals($developer->fullname, trans('tinyissue.anonymous'));
        auth()->login($developer);
        $this->assertNotEquals($developer->fullname, trans('tinyissue.anonymous'));
    }
}
