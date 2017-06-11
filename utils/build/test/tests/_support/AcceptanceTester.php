<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/

class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

   /**
    * Define custom actions here
    */


    public function wantTo($text)
{
parent::wantTo($text);
codecept_debug($text);
}

public function checkError()
{
$this->wantTo('Check errors');
$m = microtime(true);
$this->waitForElement('body', 5);
//codecept_debug(round(microtime(true) - $m, 2));
$text = htmlspecialchars_decode($this->grabTextFrom('body'));
return $this->checkErrorInSource($text);
}

public function checkErrorInSource(string $text)
{
foreach (['exception', 'Warning:', 'Parse error', 'Fatal error', 'Notice: Undefined'] as $err) {
if (strpos($text, $err) !== false) {
return $this->assertTrue(false, $err);
}
}
}

public function openPage(string $url)
{
$this->amOnPage($url);
$this->checkError();
}

public function getAbsoluteUrl()
{
$url = $this->executeJS('return location.href');
if ($i = strrpos($url, '#')) {
$url = substr($url, 0, $i);
}

return $url;
}

public function appendJS(string $js)
{
$js = strtr($js, [
"\\" => "\\\\",
"'" => "\\'",
"\n" => '\n',
"\r" => '',
]);

$this->executeJs('$(\'head:first\').append(\'<script type="text/javascript">' . $js . '</script>\');');
}

    public function waitForUrl(callable $callback, int $timeout = 5)
    {
        $this->executeInSelenium(
            function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) use ($callback, $timeout) {
                $webdriver->wait($timeout)->until(function() use ($callback, $webdriver) {
return $callback(\Codeception\Util\Uri::retrieveUri($webdriver->getCurrentURL()));
});
            }
        );
    }

    public function waitForUrlChanged(int $timeout = 5)
{
$this->wantTo('Wait for url changed');
$current = $this->grabFromCurrentUrl();
$this->waitForUrl(function($url) use ($current) {
return $url != $current;
}, $timeout);
}

}
