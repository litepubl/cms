<?php
namespace Page;
use litepubl\test\init;

class Installed
{
use TesterTrait;

    // include url of current page
    public static $url = '/';
      public static $email = '#email';
public static $password = '#password';
      public static $link = '#admin-login';

public function saveAccount()
{
$i = $this->tester;
$i->wantTo('Save admin account');

$admin = init::$admin;
$admin->email = $i->grabTextFrom(static::$email);
$admin->password = $i->grabTextFrom(static::$password);
$admin->save();

$i->click(static::$link);
$i->checkError();

return $this;
}

}