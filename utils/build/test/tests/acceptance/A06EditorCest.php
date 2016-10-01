<?php
namespace litepubl\tests\acceptance;

use test\Config;

class A06EditorCest extends \Page\Editor
{

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test post editor');
        $lang = config::getLang();
        $data = $this->load('editor');

        $i->wantTo('Open new post editor');
        $this->open();
        $this->screenShot('new');
        $this->upload('img1.jpg');
        $i->checkError();

        $i->wantTo('Fill title and content');
        $this->fillTitleContent($data->title, $data->content);
        $this->screenShot('title');

        $i->wantTo('Select category');
        $i->checkOption($this->category);
        $this->screenShot('category');

        $i->wantTo('test date time tab');
        $this->clickTab($lang->posted);
        $i->checkError();
        $this->screenShot('datetab');
        $i->see($lang->date);

        $i->wantTo('Open dialog with calendar');
        $i->click($this->calendar);
        $this->waitForOpenDialog();
        $i->waitForElement($this->datePicker);
        $this->screenShot('calendar');
        $i->click(['link' => '2']);
        $i->click($data->close);
        $this->waitForcloseDialog();
        $this->screenShot('tosave');
        $this->submit();
        $this->screenShot('saved');

    }
}
