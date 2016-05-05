<?php
namespace Page;

class Login
{
use TesterTrait;

    // include url of current page
    public static $url = '/admin/login/';
      public static $email = '#form-login [name=email]';
public static $password = '#password-password';
      public static $submit = '#submitbutton-log_in';

public function login($email, $password)
{
$i = $this->tester;
$i->wantTo('log in');
$i->openPage(static::$url);
$i->fillField(static::$email, $email);
$i->fillField(static::$password, $password);
$i->click(static::$submit);
$i->checkError();

return $this;
}

}