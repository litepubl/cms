<?php
namespace litepubl\tests\shop;

class S02BoardCest extends \Page\Base
{
    protected $url = '/admin/shop/';
    protected $failUrl = '/admin/cabinet/fail/';
    protected $cabinetUrl = '/admin/cabinet/';
    protected $error = '.text-warning';

    protected function test(\AcceptanceTester $i)
    {
        $i->openPage('/');
        $i->wantTo('Test admin shop dashboard');
        $this->login();
        $i->openPage($this->url);
        $this->check('board');

        $i->openPage($this->cabinetUrl);
        $this->check('cabinet');

        $i->openPage($this->failUrl);
        $this->check('fail');

    }

    protected function check(string $name)
    {
        $this->tester->wantTo("Exists $name page");
        $this->tester->dontSeeElement($this->error);
        $this->screenshot($name);
    }
}
