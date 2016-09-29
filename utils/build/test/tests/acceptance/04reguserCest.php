<?php
namespace litepubl\test\acceptance;

use litepubl\utils\Filer;
use test\Utils;
use test\config;

class RegUserCest extends PasswordCest
{
    protected $regUrl = '/admin/reguser/';
    protected $optionsUrl = '/admin/options/secure/';
    protected $groupsUrl= '/admin/users/options/';
    protected $enabled = 'input[name=usersenabled]';
    protected $reguser = 'input[name=reguser]';
    protected $cmtCheckbox= 'input[name=idgroup-5]';
    protected $email = '[name=email]';
    protected $name = '[name=name]';
    protected $submit = '#submitbutton-signup';

    protected $url = '/admin/password/';
    protected $email = '#form-lostpass [name=email]';
    protected $password = '.password';
    protected $submit = '#submitbutton-send';

    protected function test(\AcceptanceTester $i)
    {
$i->wantTo('Enable user registration');
$this->open($this->optionsUrl);
$i->checkOption($this->enabled);
$i->checkOption($this->reguser);
$this->screenshot('options');
$i->click($this->updateButton);
$i->checkError();

$i->wantTo('Add commentator to default group');
$i->openPage($this->groupsUrl);
$i->checkOption($this->cmtCheckbox);
$this->screenshot('groups');
$i->click($this->updateButton);
$i->checkError();

$this->logout();
$this->open();
$i->wantTo('Register new user');
$user = $this->load('reguser');
$user->email = time() . $user->email;
$i->fillField($this->email, $user->email);
$i->fillField($this->name, $user->email);
$this->screenshot('regform');
$password->removeLogs();
$i->click($this->submit);
$i->checkError();
$this->screenshot('confirm');

$password->confirmEmail();
$this->screenshot('confirmed');

$i->wantTo('Logon as new user');
$i->openPage('/admin/');
$i->checkError();
$this->screenshot('logged');

$i->wantTo('Check restore password');
$this->logout();

$password->removeLogs();
$i->wantTo('Open restore password page');
$i->openPage($password->url);

$user->password = $password->restore($user->email);
$i->wantTo('Login with new password');
$i->openPage($this->loginUrl);
$this->authAccount($user->email, $user->password);
$this->logout();
}
}