<?php


use test\config;
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

public function checkError()
{
$this->wantTo('Check errors');
$this->dontSee('exception');
$this->dontSee('warning');
$this->dontSee('Parse error');
$this->dontSee('Fatal error');
$this->dontSee('Notice: Undefined');
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

public function screenShot(string $name)
{
if (config::$screenshot) {
$this->makeScreenshot($name);
}
}

public function appendJS(string $js)
{
$js = strtr($js, [
"'" => "\\'",
"\n" => '\n',
"\r" => '',
]);

$this->executeJs('$(\'head:first\').append(\'<script type="text/javascript">' . $js . '</script>\');');
}

}
