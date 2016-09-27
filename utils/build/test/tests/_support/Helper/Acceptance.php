<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{

public function saveHtml(string $name)
{
    $this->getModule('WebDriver')->_savePageSource(codecept_output_dir()."$name.html");
}

public function setConfigUrl(string $url)
{
$this->getModule('WebDriver')->_reconfigure(['url' => $url]);
}

}
