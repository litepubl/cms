<?php
namespace Page;

use test\config;

class Base
{
public $loginUrl = '/admin/login/';
    public $logoutUrl = '/admin/logout/';
    protected $tester;

    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

public function load($name)
{
return config::load($name);
}

public function logout()
{
$i = $this->tester;
$i->wantTo('log out');
$i->openPage($this->logoutUrl);
return $this;
}

public function login()
{
$i = $this->tester;
$login = Login::i($i);
$i->openPage($login->url);
$login->login();
return $this;
}

public function open()
{
$i = $this->tester;
$i->wantTo('Open page');
$i->maximizeWindow();
$i->openPage($this->url);
$url = $i->grabFromCurrentUrl();
codecept_debug($url);
if ($this->url != $url) {
$this->login();
$i->openPage($this->url);
$i->seeCurrentUrlEquals($this->url);
}

return $this;
}

    }
