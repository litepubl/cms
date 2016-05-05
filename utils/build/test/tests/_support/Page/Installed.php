<?php
namespace Page;
use litepubl\test\config;

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

$data = [
'email' => $i->grabTextFrom(static::$email),
'password' => $i->grabTextFrom(static::$password),
];

config::save('admin', $data);

$i->wantTo('Open login page');
$i->click(static::$link);
$i->checkError();

return $this;
}

}