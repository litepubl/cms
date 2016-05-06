<?php
namespace Page;
use test\config;

class Password
{
use TesterTrait;

    // include url of current page
    public static $url = '/admin/password/';
    public static $logoutUrl = '/admin/logout/';
      public static $email = '#form-lostpass [name=email]';
public static $password = '#password-password';
      public static $submit = '#submitbutton-send';

public function logout()
{
$i = $this->tester;
$i->wantTo('log out');
$i->openPage(static::$logoutUrl);
return $this;
}

public function restore()
{
$admin = config::load('admin');
$i = $this->tester;
$i->wantTo('Send emailin');
$i->fillField(static::$email, $admin->email);
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