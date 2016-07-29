<?php

use Html as Html;

class HtmlExtensionTest extends \Codeception\TestCase\Test
{
    public function testBlueBox()
    {
        $data = [
            'content'   => 'Content...',
            'style'     => 'blue-box',
            'title'     => ['Box Title', 'http://my.geek.nz'],
            'moreLink'  => 'http://my.geek.nz',
            'moreTitle' => 'My Geek NZ',
        ];
        extract($data);

        $box = Html::box($content, $style, $title, $moreLink, $moreTitle);

        foreach ($data as $item) {
            if (is_array($item)) {
                $item = '<a href="' . $item[1] . '">' . $item[0] . '</a>';
            }
            $this->assertContains($item, $box);
        }
    }

    public function testDateTime()
    {
        $this->assertEquals('5 secs', Html::duration(5));

        $date = new \DateTime();
        $date->sub(new \DateInterval('P9D'));
        $this->assertEquals('1 week ago', Html::age($date->format('Y-m-d')));
    }
}
