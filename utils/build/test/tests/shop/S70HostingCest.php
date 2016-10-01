<?php

namespace litepubl\tests\shop;

class S70HostingCest extends \Page\Base
{
    protected function test(\AcceptanceTester $i)
    {
$i->wantTo('Test install and uninstall hosting');
$data = $this->load('shop/hosting');
$ulogin = new Ulogin($i, '170hosting');
$this->reInstall('hosting', 160);

$i->openPage($this->url);
$i->wantTo('Create new category');
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

$i->wantTo('Check cabinet');
$this->logout();
$ulogin->login();
$i->openPage($this->cabinetUrl);
$i->openPage($this->addUrl);
$i->fillField($this->title, $data->title);
$i->fillField($this->text, $data->text);
$this->screenshot('create');
$i->click($this->addButton);
$i->checkError();

$i->wantTo('Add message to ticket');
$i->fillField($this->message, $data->message);
$this->screenshot('addmessage');
$i->click($this->send);
$i->checkError();
$this->screenshot('messages');
$this->logout();
}
}