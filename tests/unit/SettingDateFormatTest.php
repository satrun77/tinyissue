<?php

class SettingDateFormatTest extends \Codeception\TestCase\Test
{
    protected $timestamp = 'January 14th, 2016 2:42 PM';

    public function testChangeDateFormat()
    {
        /** @var \Tinyissue\Services\SettingsManager $settings */
        $settings = app('tinyissue.settings');
        $format1  = $settings->getDateFormat();
        $date1    = \Html::date($this->timestamp);

        $this->assertEquals($date1, $this->getDate($format1));

        $format2 = 'Y-m-d';
        $settings->save([
            'date_format' => $format2,
        ]);

        $this->assertNotEquals($date1, $this->getDate($format2));
        $this->assertEquals(\Html::date($this->timestamp), $this->getDate($format2));
    }

    /**
     * Returns formatted date.
     *
     * @param $format
     *
     * @return string
     */
    protected function getDate($format)
    {
        $date = new \DateTime($this->timestamp);

        return $date->format($format);
    }
}
