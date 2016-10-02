<?php

namespace litepubl\tests\shop;

class S70HostingCest extends \shop\Hosting
{
protected function test(\AcceptanceTester $i)
{
    $i->wantTo('Test install and uninstall hosting');
    $this->reInstallPlugin('hosting', 160);
    $this->ulogin = $this->getUlogin();

    $server = $this->data->servers->preinstalled;
    $this->addServer($server->type);
    $this->setAccFile($server->accfile);

$this->addAccount();
$release = $this->data->release;
$this->editAccount($release);
}
}