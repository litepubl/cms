<?php
namespace Page;

use test\config;

class Base
{
public $loginUrl = '/admin/login/';
    public $logoutUrl = '/admin/logout/';
public $updateButton = '#submitbutton-update';
public $screenshotName = '00base';
protected $screenshotIndex = 0;
    protected $tester;
private $tabJS;

    public function __construct(\AcceptanceTester $I, string $screenshotName = '')
    {
        $this->tester = $I;
if ($screenshotName) {
$this->screenshotName = $screenshotName;
}
    }

public function load($name)
{
return config::load($name);
}

public function logout()
{
$i = $this->tester;
$i->wantTo('log out');
$i->openPage($this->logoutUrl);
return $this;
}

public function login()
{
$i = $this->tester;
$login = Login::i($i);
$i->openPage($login->url);
$login->login();
return $this;
}

public function open(string $url = '')
{
$i = $this->tester;
$i->wantTo('Open page');
$i->maximizeWindow();

if (!$url) {
$url = $this->url;
}

$i->openPage($url);
$cur = $i->grabFromCurrentUrl();
codecept_debug($cur);
if ($url != $cur) {
$this->login();
$i->openPage($url);
$i->seeCurrentUrlEquals($url);
}

return $this;
}

public function clickTab(string $tab)
{
$i = $this->tester;
if (!$i->executeJS('return "flagLoaded" in litepubl.tabs;')) {
if (!$this->tabJS) {
$this->tabJS = file_get_contents(__DIR__ . '/js/tabs.js');
}

$i->appendJS($this->tabJS);
}

$i->click($tab);
$i->waitForJS('return litepubl.tabs.flagLoaded');
//$i->checkError();
}

public function screenshot(string $subname)
{
$this->tester->screenshot(sprintf('%s.%02d%s', $this->screenshotName, $this->screenshotIndex++, $subname));
}

    }
