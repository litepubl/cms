<?php
namespace litepubl\tests\acceptance;

use litepubl\utils\Filer;
use test\Utils;
use test\config;

class A03PasswordCest extends \Page\Base
{
    protected $url = '/admin/password/';
    protected $email = '#form-lostpass [name=email]';
    protected $password = '.password';
    protected $submit = '#submitbutton-send';

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test restore password');
        $this->logout();
        $this->removeLogs();

        $i->wantTo('Open restore password page');
        $i->openPage($this->url);
        $this->screenShot('form');
        $admin = $this->getAdminAccount();
        $admin->password = $this->restore($admin->email);
        config::save('admin', $admin);
        $this->screenShot('restored');

        $i->wantTo('Login with new password');
        $i->openPage($this->loginUrl);
        $this->authAccount($admin->email, $admin->password);

    }

    protected function removeLogs()
    {
        Filer::delete(config::$home . '/storage/data/logs/', false, false);
    }

    protected function restore(string $email)
    {
        $i = $this->tester;
        $i->wantTo('Send email');
        $i->fillField($this->email, $email);
        $i->click($this->submit);
        $i->checkError();
        $this->confirmEmail();
        return $i->grabTextFrom($this->password);
    }

    protected function confirmEmail()
    {
        $i = $this->tester;
        $i->wantTo('Grab url from email');
        $s = Utils::getSingleFile(config::$home . '/storage/data/logs/');
        $i->assertFalse(empty($s), 'Email file not found');
        $url = Utils::getLine($s, '&confirm=');
        $i->assertNotEmpty($url, 'Url not found in email');
        $i->amOnUrl($url);
        $i->checkError();
    }
}
