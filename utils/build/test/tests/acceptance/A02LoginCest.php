<?php
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