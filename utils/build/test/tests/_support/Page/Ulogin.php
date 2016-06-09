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

public $twitterLogin = '#username_or_email';
public $twitterPassword = '#password';
public $twitterSubmit = '#allow';
public $twitterCancel = '#cancel';
public $twitterAllow = '#allow';
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
$this->tester->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
     $webdriver->switchTo()->window($this->winhandles[$index]);
});
}

public function auth(string $net = 'twitter')
{
$i = $this->tester;
$i->wantTo('Wait for load ulogin widget');
$i->appendJS(file_get_contents(__DIR__ . '/js/ulogin.js'));
$i->waitForJS('return litepubl.uloginopened;', 6);
$i->screenshot('20.ulogin.01wait');
$i->wantTo('Switch to new window');
$name = 'mailru';
$data = $this->load($name);
$this->getwindows();
$i->maximizeWindow();

switch ($name) {
case 'mailru':
$i->waitForElementVisible($this->mailruLogin, 10);
$i->fillField($this->mailruLogin, $data->login);
$i->fillField($this->mailruPassword, $data->password);
$i->wantTo('Auth app');
$i->screenshot('20.ulogin.02auth');
$i->click($this->mailruSubmit);
$i->screenshot('20.ulogin.03allow');
//sleep(4);
$i->savehtml('auth');
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

case 'twitter':
$i->waitForElementVisible($this->twitterLogin, 10);
$i->fillField($this->twitterLogin, $data->login);
$i->fillField($this->twitterPassword, $data->password);
$i->wantTo('Auth app');
$i->screenshot('20.ulogin.02auth');
$i->click($this->twitterSubmit);
$i->screenshot('20.ulogin.03allow');
$i->savehtml('auth');
$i->click($this->twitterAllow);
break;
}

$this->setWindow(0);
sleep(3);
$i->savehtml('logged');
}

}
