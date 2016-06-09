<?php
namespace Page;

class Ulogin extends Base
{
    public  $url = '/admin/ulogin.php';
public $twitterEmail = '#username_or_email';
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
$i->waitForJS('return litepubl.uloginclicked;', 6);
$i->screenshot('20.ulogin.01wait');
$i->wantTo('Switch to new window');
$data = $this->load('twitter');
$this->getwindows();
//codecept_debug(var_export($this->winhandles, true));
$i->waitForElementVisible($this->twitterEmail, 20);
$i->fillField($this->twitterEmail, $data->email);
$i->fillField($this->twitterPassword, $data->password);
$i->wantTo('Auth app');
$i->screenshot('20.ulogin.02auth');
$i->click($this->twitterSubmit);
$i->screenshot('20.ulogin.03allow');
$i->savehtml('auth');
$i->click($this->twitterAllow);
$this->setWindow(0);
sleep(3);
$i->savehtml('logged');
}

}
