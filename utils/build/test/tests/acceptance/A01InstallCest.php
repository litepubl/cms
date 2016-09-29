<?php
namespace litepubl\tests\acceptance;

use test\config;
use litepubl\utils\Filer;

class A01InstallCest extends \Page\Base
{
    protected $url = '/';
    protected $langForm = '#langform';
    protected $langCombo = '#combo-lang';
    protected $langSubmit= '#changelang';
    protected $form = '#form';
    protected $email = '#text-email';
    protected $name = '#text-name';
    protected $description = '#text-description';
    protected $dbname = '#text-dbname';
    protected $dblogin= '#text-dblogin';
    protected $dbpassword = '#text-dbpassword';
    protected $dbprefix = '#text-dbprefix';
    protected $submit = '#submitbutton-createblog';
    protected $emailText = '#email';
    protected $passwordText = '#password';
    protected $loginLink = '#admin-login';

    protected function test(\AcceptanceTester $i)
    {
if (config::exists('admin')) {
    codecept_debug('Install skiped');
    return;
}

$this->removeData();
$i->wantTo('Open install form');
$i->openPage($this->url);
$this->screenShot('form');

//$this->switchLanguages();
$i->wantTo('Fill install form');
$data = $this->load('install');
$i->fillField($this->email, $data->email);
$i->fillField($this->name, $data->name);
$i->fillField($this->description, $data->description);
$i->fillField($this->dbname, $data->dbname);
$i->fillField($this->dblogin, $data->dblogin);
$i->fillField($this->dbpassword, $data->dbpassword);
$i->fillField($this->dbprefix, $data->dbprefix);
$this->screenshot('form');
$i->click($this->submit);
$i->checkError();
$i->assertFileExists(config::$home . '/storage/data/storage.inc.php', 'CMS not installed: storage not found');
$this->saveAccount();
$this->screenShot('installed');
$i->wantTo('Open login page');
$i->click($this->loginLink);
$i->checkError();
}

    protected function switchLanguages()
    {
        $this->tester->wantTo('Switch languages');
        $this->changeLanguage('English');
        $this->changeLanguage('Russian');
    }

    protected function changeLanguage($name)
    {
        $i = $this->tester;
        $i->wantTo('Switch language');
        $i->selectOption($this->langCombo, $name);
        $i->click($this->langSubmit);
        $i->checkError();
    }

    protected function removeData()
    {
        $i = $this->tester;
        $i->wantTo('Remove data files');

        Filer::delete(config::$home . '/storage/data', true, false);
        Filer::delete(config::$home . '/storage/cache', true, false);
        Filer::delete(config::$home . '/files/js', true, false);
        Filer::delete(config::$home . '/files/image', true, false);
        Filer::delete(config::$home . '/files/imgresize', true, false);

        $i->assertFileNotExists(config::$home . '/storage/data/index.htm', 'Data folder not empty');
    }

    protected function saveAccount()
    {
        $i = $this->tester;
        $i->wantTo('Save admin account');

        $data = [
        'email' => $i->grabTextFrom($this->emailText),
        'password' => $i->grabTextFrom($this->passwordText),
        ];

        config::save('admin', $data);
    }
}
