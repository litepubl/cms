<?php
namespace Page;
use test\config;

class Login
{
use TesterTrait;

    // include url of current page
    public static $url = '/admin/login/';
    public static $logoutUrl = '/admin/logout/';
      public static $email = '#form-login [name=email]';
public static $password = '#password-password';
      public static $submit = '#submitbutton-log_in';

public function logout()
{
$i = $this->tester;
$i->wantTo('log out');
$i->openPage(static::$logoutUrl);
return $this;
}

public function login()
{
$admin = config::load('admin');
return $this->auth($admin->email, $admin->password);
}

public function auth($email, $password)
{
$i = $this->tester;
$i->wantTo('log in');
$i->fillField(static::$email, $email);
$i->fillField(static::$password, $password);
$i->click(static::$submit);
$i->checkError();
return $this;
}

public function check()
{
$i = $this->tester;
$i->wantTo('Wheare are');
$url = $i->grabFromCurrentUrl();
codexcept_debug($url);

if (strpos($url, static::$url)) {
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
$i->openPage(static::$url);
}

}