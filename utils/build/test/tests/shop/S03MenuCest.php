<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\tests\shop;

class S03MenuCest extends \litepubl\tests\acceptance\A05AdminCest
{
    protected $url = '/admin/shop/';
    protected $cabinetUrl = '/admin/cabinet/';

    protected function submitForm()
    {
        $i = $this->tester;
        $i->executeJs('$(function(){$("form:last").submit();});');
        sleep(1);
    }

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test shop admin panel');
        $this->open();

        $list = $this->getLinks('shop/forms');
        foreach ($list as $url) {
            $i->wantTo("Test form $url");
            $i->openPage('/admin/' . $url);
            $this->submit();
        }

        $this->open();
        $list = $this->getMenu();
        foreach ($list as $j => $url) {
            //codecept_debug($url);
            $i->wantTo("Test page $url");
            $i->amOnUrl($url);
            $i->checkError();
            $this->screenShot(str_replace('/', '-', trim($url, '/')));
            $this->submitForm();
            $i->checkError();
        }

        $this->open($this->cabinetUrl);
        $list = $this->getMenu();
        foreach ($list as $j => $url) {
            //codecept_debug($url);
            $i->wantTo("Test page $url");
            $i->amOnUrl($url);
            $i->checkError();
            $this->screenShot(str_replace('/', '-', trim($url, '/')));
            $this->submitForm();
            $i->checkError();
        }

    }
}
