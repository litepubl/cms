<?php
namespace litepubl\test\acceptance;

class LoginCest extends \Page\Login
{
    protected function test(\AcceptanceTester $i)
    {
$i->wantTo('Test login and logout');
$i->openPage($this->url);
$this->screenShot('form');

$login->login();
$i->seeCurrentUrlEquals('/admin/');
$this->screenShot('board');

$login->logout();
$i->seeCurrentUrlEquals($login->url);
}
}