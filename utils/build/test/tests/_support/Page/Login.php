<?php
namespace Page;
use test\config;

class Login extends Base
{
use Singleton;

    public $url = '/admin/login/';
      public $email = '#form-login [name=email]';
public $password = '#password-password';
      public $submit = '#submitbutton-log_in';
private $admin;

public function login()
{
if (!$this->admin) {
$this->admin = config::load('admin');
}

return $this->auth($this->admin->email, $this->admin->password);
}

public function auth($email, $password)
{
$i = $this->tester;
$i->wantTo('log in');
$i->fillField($this->email, $email);
$i->fillField($this->password, $password);
$i->click($this->submit);
$i->checkError();
return $this;
}

public function check()
{
$i = $this->tester;
$i->wantTo('Wheare are');
$url = $i->grabFromCurrentUrl();
codexcept_debug($url);

if (strpos($url, $this->url)) {
$this->login();
} else {
$this->open();
}

return $this;
}

public function open()
{
$i = $this->tester;
$i->wantTo('Open login page');
$i->openPage($this->url);
return $this;
}

}