<?php
namespace Page;
use test\config;
use test\Utils;

class Password
{
use TesterTrait;

    // include url of current page
    public static $url = '/admin/password/';
    public static $logoutUrl = '/admin/logout/';
      public static $email = '#form-lostpass [name=email]';
public static $password = '.password';
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
$i->wantTo('Send email');
$i->fillField(static::$email, $admin->email);
$i->click(static::$submit);
$i->checkError();

$i->wantTo('Grab url from email');
$s = Utils::getSingleFile(config::$home . '/storage/data/logs/');
$i->assertFalse(empty($s), 'Email file not found');
$url = Utils::getLine($s, '&confirm=');
$i->assertNotEmpty($url, 'Url not found in email');
$i->amOnUrl($url);
$i->checkError();
$admin->password = $i->grabTextFrom(static::$password);
config::save('admin', $admin);

$login = new Login($i);
$i->openPage($login::$url);
$login->auth($admin->email, $admin->password);

return $this;
}

}