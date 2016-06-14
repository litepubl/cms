<?php
namespace Page;

class Ulogin extends Base
{
    public  $url = '/admin/ulogin.php';
public $mailruLogin = '#login';
public $mailruPassword = '#password';
public $mailruSubmit = 'Log in and allow';

public $yandexLogin = '#login';
public $yandexPassword = '#passwd';
public $yandexSubmit = 'button[type=submit]';
public $yandexAllow = '.authrequest-request-allow button';
public $yandexCancel = '.authrequest-request-deny button';
private $winhandles = [];

public function getwindows()
{
$this->tester->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
     $this->winhandles=$webdriver->getWindowHandles();
     $webdriver->switchTo()->window($this->winhandles[1]);
//$this->tester->savehtml($i);
});
}

public function setWindow(int $index)
{
$this->tester->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) use ($index)
{
     $webdriver->switchTo()->window($this->winhandles[$index]);
});
}

public function click(string $name = 'mailru')
{
$i = $this->tester;
$i->wantTo("click $name button");
codecept_debug($i->executeJs('return litepubl.authdialog.ulogin.status'));
//$i->waitForJS('return litepubl.authdialog.ulogin.status == \'ready\';', 8);
$i->waitForJS('return litepubl.authdialog.ulogin.status != \'wait\';', 8);
codecept_debug($i->executeJs('return litepubl.authdialog.ulogin.status'));
$i->executeJs("\$('[data-uloginbutton=$name]').click();");
codecept_debug($i->executeJs('return litepubl.authdialog.ulogin.status'));
$i->waitForJS('return litepubl.authdialog.ulogin.status == \'open\';', 5);
}

public function waitForcloseDialog()
{
$i = $this->tester;
$i->wantTo('Close auth dialog');
$i->waitForJS('return !litepubl.authdialog.dialog;', 5);
}

public function auth(string $name = 'mailru')
{
$i = $this->tester;
$i->wantTo('Switch to new window');
$this->getwindows();
//$i->maximizeWindow();

switch ($name) {
case 'mailru':
$this->mailruAuth();
break;

case 'yandex':
$this->yandexAuth();
break;

default:
throw new \RuntimeException('Unknown net');
}

$i->wantTo('Switch to back window');
$this->setWindow(0);
codecept_debug($i->executeJS('return litepubl.authdialog.ulogin.status;'));
}

public function mailruAuth()
{
$data = $this->load('mailru');
$i = $this->tester;
$i->waitForElementVisible($this->mailruLogin, 10);
$this->screenshot('auth');
$i->fillField($this->mailruLogin, $data->login);
$i->fillField($this->mailruPassword, $data->password);
//$i->savehtml('form');
$i->wantTo('Auth app');
//$i->click($this->mailruSubmit);
$i->executeJS(file_get_contents(__DIR__ . '/js/mailruSubmit.js'));
}

public function yandexAuth()
{
$data = $this->load('yandex');
$i = $this->tester;
$i->waitForElementVisible($this->yandexLogin, 10);
$i->screenshot('20.ulogin.01auth');
$i->fillField($this->yandexLogin, $data->login);
$i->fillField($this->yandexPassword, $data->password);
$i->wantTo('Auth app');
$i->click($this->yandexSubmit);
$i->screenshot('20.ulogin.03allow');
$i->savehtml('auth');
$i->click($this->yandexAllow);
}

}
