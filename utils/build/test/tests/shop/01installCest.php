<?php
namespace litepubl\test\shop;

use Page\Ulogin;

class InstallCest extends \Page\Base
{
    public function tryToTest(AcceptanceTester $i)
    {
$i->wantTo('Test install and uninstall shop plugin');
$this->setTester($i);
$this->installPlugin('jslogger', 160);

if ($i->executeJs('return $("input[name=base]").prop("checked");')) {
    codecept_debug('Skip shop install');
    return;
}

$this->reInstallPlugin('base', 160);
$this->installPlugin('real', 160);
}
}