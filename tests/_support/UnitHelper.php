<?php

namespace Codeception\Module;

use Tinyissue\Creatables;
use Tinyissue\Fetchables;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class UnitHelper extends \Codeception\Module
{
    use Creatables, Fetchables;
}
