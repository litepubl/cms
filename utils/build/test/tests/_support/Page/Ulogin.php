<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace Page;

class Ulogin extends Base
{
    public $url = '/admin/ulogin.php';
    public $mailruLogin = '#login';
    public $mailruPassword = '#password';
    public $mailruSubmit = 'Log in and allow';
public $usersUrl = '/admin/users/';
public $deleteButton = '#submitbutton-delete';
    public $yandexLogin = '#login';
    public $yandexPassword = '#passwd';
    public $yandexSubmit = 'button[type=submit]';
    public $yandexAllow = '.authrequest-request-allow button';
    public $yandexCancel = '.authrequest-request-deny button';
    private $winhandles = [];
protected static $logged = false;

    protected function getwindows()
    {
        $this->tester->executeInSelenium(
            function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
                $this->winhandles=$webdriver->getWindowHandles();
                $webdriver->switchTo()->window($this->winhandles[1]);
                //$this->tester->savehtml($i);
            }
        );
    }

    protected function setWindow(int $index)
    {
        $this->tester->executeInSelenium(
            function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) use ($index) {
                $webdriver->switchTo()->window($this->winhandles[$index]);
            }
        );
    }

    protected function click(string $name = 'mailru')
    {
        $i = $this->tester;
        $i->wantTo("click $name button");
        codecept_debug($i->executeJs('return litepubl.authdialog.ulogin.status'));
        $i->waitForElement("[data-uloginbutton=$name]", 10);
       $i->waitForJS('return litepubl.authdialog.ulogin.status != \'wait\';', 7);
        codecept_debug($i->executeJs('return litepubl.authdialog.ulogin.status'));
        $i->wantTo('Click social network button');
        $i->executeJs("\$('[data-uloginbutton=$name]').click();");
        codecept_debug($i->executeJs('return litepubl.authdialog.ulogin.status'));
        $i->wantTo('Wait to open window');
        $i->waitForJS('return (litepubl.authdialog.ulogin.status == \'open\' || litepubl.authdialog.ulogin.status == \'receive\');', 5);
    }

    protected function waitForcloseDialog()
    {
        $i = $this->tester;
        $i->wantTo('Close auth dialog');
        codecept_debug(date('i:s'));
        $i->waitForJS('return !litepubl.authdialog.dialog;', 5);
        codecept_debug(date('i:s'));
        $i->wantTo('Check user logged');
        $i->waitForJS('return litepubl.getuser().id;', 15);
        codecept_debug(date('i:s'));
    }

    protected function auth(string $name = 'mailru')
    {
        $i = $this->tester;
        $i->wantTo('Switch to new window');
        $this->getwindows();

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
static::$logged = true;
    }

    public function _login()
    {
        $i = $this->tester;
        $cur = $i->grabFromCurrentUrl();
        codecept_debug($cur);
        if (strpos($cur, $this->loginUrl) !== 0) {
            $i->openPage($this->loginUrl);
        }

sleep(2);
$this->click();
if (!static::$logged) {
$this->auth();
}

$i->waitForUrlChanged(10);
}

    protected function mailruAuth()
    {
        $data = $this->load('mailru');
        $i = $this->tester;
        $i->waitForElementVisible($this->mailruLogin, 10);
        $this->screenshot('auth');
        $i->fillField($this->mailruLogin, $data->login);
        $i->fillField($this->mailruPassword, $data->password);
        //$i->savehtml('form');
        $i->wantTo('Auth app');
        $i->executeJS($this->getFile(__DIR__ . '/js/mailruSubmit.js'));
    }

    protected function yandexAuth()
    {
        $data = $this->load('yandex');
        $i = $this->tester;
        $i->waitForElementVisible($this->yandexLogin, 10);
        $i->screenshot('yandex');
        $i->fillField($this->yandexLogin, $data->login);
        $i->fillField($this->yandexPassword, $data->password);
        $i->wantTo('Auth app');
        $i->click($this->yandexSubmit);
        $i->screenshot('allow');
        //$i->savehtml('auth');
        $i->click($this->yandexAllow);
    }

protected function deleteUser()
{
        $this->open($this->usersUrl);
        $i = $this->tester;
        $i->wantTo('Delete last user');
        $i->executeJs('$("[name^=\'user-\']").prop("checked", true)');
        $i->click($this->deleteButton);
        $i->checkError();
}
}
