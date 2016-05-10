<?php
namespace Page;
use test\config;
use test\Utils;

class Password extends Base
{
    public  $url = '/admin/password/';
      public  $email = '#form-lostpass [name=email]';
public  $password = '.password';
      public  $submit = '#submitbutton-send';

public function restore()
{
$i = $this->tester;
$login = Login::i($i);
$admin = $login->getAdmin();

$i->wantTo('Send email');
$i->fillField($this->email, $admin->email);
$i->click($this->submit);
$i->checkError();

$i->wantTo('Grab url from email');
$s = Utils::getSingleFile(config::$home . '/storage/data/logs/');
$i->assertFalse(empty($s), 'Email file not found');
$url = Utils::getLine($s, '&confirm=');
$i->assertNotEmpty($url, 'Url not found in email');
$i->amOnUrl($url);
$i->checkError();
$admin->password = $i->grabTextFrom($this->password);
config::save('admin', $admin);

$i->openPage($login->url);
$login->auth($admin->email, $admin->password);

return $this;
}

}