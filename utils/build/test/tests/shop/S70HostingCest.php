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

$id = $this->addAccount();
$release = $this->data->release;
$this->editAccount($release);
$this->testRun($id);
$this->setIdRelease($id);
$this->createRelease();

$i->openPage($this->optionsUrl);
$this->submit();

$i->wantTo('Add reg domain');
$i->openPage($this->regdomainsUrl);
$i->click($this->addRegDomain);
sleep(3);
$i->checkError();
$i->wantTo('Save reg domain options');
$this->submit();
$i->wantTo('Check tarrifs');
$this->addTariff();


	



}
}