<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace Page;

use test\config;

class Base
{
    public $loginUrl = '/admin/login/';
    public $logoutUrl = '/admin/logout/';
    public $updateButton = '#submitbutton-update';
    public $postlink= '.post-bookmark';
    public $screenshotName = '00base';
    protected $screenshotIndex = 1;
    protected $tester;
    protected $cacheFiles = [];

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
        $cur = $i->grabFromCurrentUrl();
        codecept_debug($cur);
        if (strpos($cur, $login->url) !== 0) {
            $i->openPage($login->url);
        }
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
    }

    public function submit()
    {
        $i = $this->tester;
        $i->click($this->updateButton);
        $i->checkError();
    }

    public function clickTab(string $tab)
    {
        $i = $this->tester;
        if ($i->executeJS('return "flagLoaded" in litepubl.tabs;')) {
            $i->executeJs('litepubl.tabs.flagLoaded = false;');
        } else {
            $i->appendJS($this->getFile(__DIR__ . '/js/tabs.js'));
        }

        $i->click($tab);
        codecept_debug(var_export($i->executeJs('return litepubl.tabs.flagLoaded'), true));
        codecept_debug(var_export($i->executeJs('return litepubl.tabs.ajax'), true));
        $i->waitForJS('return litepubl.tabs.flagLoaded', 3);
        //$i->checkError();
    }

    public function screenshot(string $subname)
    {
        $this->tester->screenshot(sprintf('%s.%02d%s', $this->screenshotName, $this->screenshotIndex++, $subname));
    }

    public function getFile(string $filename)
    {
        if (!isset($this->cacheFiles[$filename])) {
            //remove copright
            $s = file_get_contents($filename);
            if ((substr($s, 0, 2) == '/*') && ($i = strpos($s, '*/'))) {
                        $s = trim(substr($s, $i + 2));
            }

            $this->cacheFiles[$filename] = $s;
        }

        return $this->cacheFiles[$filename];
    }

    public function js(string $filename)
    {
        return $this->tester->executeJs($this->getFile(__DIR__ . '/js/' . $filename));
    }

    public function upload(string $filename)
    {
        $i = $this->tester;
        $selector   = $i->executeJs($this->getFile(__DIR__ . '/js/addTmpUpload.js'));
        $i->attachFile($selector, $filename);
        $i->checkError();
    }

    public function waitForOpenDialog()
    {
        $this->tester->wantto('Wait open dialog');
        $this->js('dialog.js');
        $this->tester->waitForJs('return litepubl.dialogOpened;', 4);
    }

    public function waitForCloseDialog()
    {
        $this->tester->wantto('Wait close dialog');
        $this->js('dialog.js');
        $this->tester->waitForJs('return !litepubl.dialogOpened;', 4);
    }

public function exists(string $selector): bool
{
$selector = str_replace("'", '"', $selector);
return $this->tester->executeJs("return \$('$selector').length;") > 0;
}
}
