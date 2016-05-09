<?php
namespace Page;

class Base
{
public $loginUrl = '/admin/login/';
    public $logoutUrl = '/admin/logout/';
    protected $tester;

    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
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
$login = Login::i();
$login->login($this->tester);
return $this;
}

    }
