<?php
namespace litepubl\tests\acceptance;

use test\Config;

class A05AdminCest extends \Page\Base
{
    protected $url = '/admin/';

    protected function getMenu()
    {
        $i = $this->tester;
        $i->wantTo('Get menu links');

        $i->waitForJS('return !! $;', 5);
        $result = $this->js('adminLinks.js');
        //delete logout link
        array_pop($result);
        return $result;
    }

    protected function submitForm()
    {
        $i = $this->tester;
        //$i->waitForJS('return !! $;', 5);
$i->waitForJS('return (typeof $ !== "undefined");', 5);
        $i->executeJs('$(function(){$("form:last").submit();});');
        usleep(300000);
    }

    protected function getLinks(string $name): array
    {
        $s = file_get_contents(config::$_data . "/$name.txt");
        $s = trim(str_replace("\r", '', $s));
        return explode("\n", $s);
    }

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test admin panel');
        $this->open();

        $list = $this->getLinks('ajaxLinks');
        foreach ($list as $url) {
            $i->wantTo("Test page $url");
            $i->openPage('/admin/' . $url);

        }

        $list = $this->getLinks('adminForms');
        foreach ($list as $url) {
            $i->wantTo("Test form $url");
            $i->openPage('/admin/' . $url);
            $this->submitForm();
            $i->checkError();
        }

        $list = $this->getMenu();
        foreach ($list as $j => $url) {
            //codecept_debug($url);
            $i->wantTo("Test page $url");
            $i->amOnUrl($url);
            $i->checkError();
            $i->waitForElement('body', 10);
            $this->screenShot(str_replace('/', '-', trim($url, '/')));
            $this->submitForm();
            $i->checkError();
            $i->waitForElement('body', 10);

        }

    }
}
