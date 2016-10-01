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
    $this->setAccFile($server->accfile);$



}


$i->openPage($this->urlCats);
$i->fillField($this->catTitle, $data->cattitle);
$this->screenshot('addcat');
$this->submit();
$this->screenshot('added');
$i->click($data->cattitle);
$i->checkError();
$i->wantTo('Delete new catalog');
$url = $i->grabFromCurrentUrl();
$i->openPage($url . '&action=delete&confirm=1');
$i->openPage($this->urlOptions);
$this->submit();
