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

public function auth(string $name = 'mailru')
{
$i = $this->tester;
$i->wantTo('Wait for load ulogin widget');
$js = file_get_contents(__DIR__ . '/js/ulogin.js');
$js = str_replace('mailru', $name, $js);
$i->appendJS($js);
//codecept_debug($i->executejs('return litepubl.ulog;'));
$i->waitForJS('return litepubl.uloginopened;', 3);
$i->screenshot('20.ulogin.01wait');
$i->wantTo('Switch to new window');

$data = $this->load($name);
$this->getwindows();
$i->maximizeWindow();

switch ($name) {
case 'mailru':
$i->waitForElementVisible($this->mailruLogin, 10);
$i->fillField($this->mailruLogin, $data->login);
$i->fillField($this->mailruPassword, $data->password);
$i->savehtml('form');
$i->wantTo('Auth app');
$i->screenshot('20.ulogin.02auth');
//$i->click($this->mailruSubmit);
$i->executeJS(file_get_contents(__DIR__ . '/js/mailruSubmit.js'));
break;

case 'yandex':
$i->waitForElementVisible($this->yandexLogin, 10);
$i->fillField($this->yandexLogin, $data->login);
$i->fillField($this->yandexPassword, $data->password);
$i->wantTo('Auth app');
$i->screenshot('20.ulogin.02auth');
$i->click($this->yandexSubmit);
$i->screenshot('20.ulogin.03allow');
$i->savehtml('auth');
$i->click($this->yandexAllow);
break;

default:
throw new \RuntimeException('Unknown net');
}

$this->setWindow(0);
codecept_debug($i->executeJS('return litepubl.ulog;'));
sleep(3);
$i->savehtml('logged');
}

}
