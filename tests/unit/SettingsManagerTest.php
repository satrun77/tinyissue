<?php

class SettingsManagerTest extends \Codeception\TestCase\Test
{
    public function testGetDefaultValue()
    {
        /** @var \Tinyissue\Services\SettingsManager $settings */
        $settings = app('tinyissue.settings');
        $value    = $settings->get('something_not_value', 99);
        $this->assertEquals(99, $value);
    }
}
