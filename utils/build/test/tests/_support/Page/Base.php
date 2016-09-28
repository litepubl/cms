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

use test\config;

class Base
{
    public $loginUrl = '/admin/login/';
    public $logoutUrl = '/admin/logout/';
public $pluginsUrl = '/admin/plugins/';
public $title = '#text-title';
    public $updateButton = '#submitbutton-update';
    public $postlink= '.post-bookmark';
    public $screenshotName;
    protected $screenshotIndex = 1;
    protected $tester;
    protected $cacheFiles = [];

    public function __construct(\AcceptanceTester $I, string $screenshotName = '')
    {
        $this->tester = $I;
        $I->maximizeWindow();
        if ($screenshotName) {
                $this->screenshotName = $screenshotName;
} else {
                $this->screenshotName = $this->getNameFromTrace();
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
sleep(1);
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
    }

    public function screenshot(string $subname)
    {
if (config::$screenshot) {
$this->tester->makeScreenshot(sprintf('%s%s.%02d%s', Config::$screenshotPrefix , $this->screenshotName, $this->screenshotIndex++, $subname));
}
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

public function getIdFromUrl(): int
{
        $ur = $this->tester->grabFromCurrentUrl();
if ($i = strpos($url, '?')) {
parse_str(substr($url, $i + 1), $a);
return (int) ($a['id'] ?? 0);
}

return 0;
}

    public function installPlugin(string $name, int $timeout = 10)
    {
        $this->open($this->pluginsUrl);
        $i = $this->tester;
        $i->wantTo("Install plugin $name");
        $i->waitForElement("input[name=$name]", 10);
        $i->checkOption("input[name=$name]");
        $i->click($this->updateButton);
        $i->checkError();
        $i->waitForElement("input[name=$name]", $timeout);
        $i->seeCheckboxIsChecked("input[name=$name]");
    }

    public function uninstallPlugin(string $name)
    {
        $this->open($this->pluginsUrl);
        $i = $this->tester;
        $i->wantTo("Uninstall plugin $name");
        $i->waitForElement("input[name=$name]", 10);
        $i->UncheckOption("input[name=$name]");
        $i->click($this->updateButton);
        $i->checkError();
        $i->waitForElement("input[name=$name]", 10);
        $i->dontSeeCheckboxIsChecked("input[name=$name]");
    }

    public function reInstallPlugin(string $name, int $timeout = 10)
{
$this->installPlugin($name, $timeout);
$this->uninstallPlugin($name);
$this->installPlugin($name, $timeout);
}

protected function getNameFromTrace(): string
{
$trace = debug_backtrace();
foreach ($trace as $item) {
if ($item['function'] == '__construct') {
$filename = basename($item['file']);

//trick with big letter C (Cept.php or Cest.php);
if ($i = strrpos($filename, 'C')) {
return substr($filename, 0, $i);
}
}
}

return '';
}
}