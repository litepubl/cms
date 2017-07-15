<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\tests\acceptance;

class A02LoginCest extends \Page\Base
{
    protected $boardUrl = '/admin/';
    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test login and logout');
        $i->openPage($this->loginUrl);
        $this->screenShot('form');
        $this->login();
        $i->seeCurrentUrlEquals($this->boardUrl);
        $this->screenShot('board');
        $this->logout();
        $i->seeCurrentUrlEquals($this->loginUrl);
    }
}
