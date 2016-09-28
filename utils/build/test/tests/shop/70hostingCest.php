<?php
//codecept g: cest shop hosting

namespace litepubl\test\shop;
use Page\Plugin;
use Page\Ulogin;
use shop\Hosting;

class HostingCest
{
    public function tryToTest(AcceptanceTester $i)
    {
$i->wantTo('Test install and uninstall hosting');
$hosting = new Hosting($i, '170hosting');
$data = $hosting->load('shop/hosting');
$ulogin = new Ulogin($i, '170hosting');
$plugin = new Plugin($i, '170hosting');
$plugin->install('hosting', 160);
$plugin->uninstall('hosting');
$plugin->install('hosting', 160);

$i->openPage($hosting->url);
$i->wantTo('Create new category');
$i->openPage($hosting->urlCats);
$i->fillField($hosting->catTitle, $data->cattitle);
$hosting->screenshot('addcat');
$hosting->submit();
$hosting->screenshot('added');
$i->click($data->cattitle);
$i->checkError();
$i->wantTo('Delete new catalog');
$url = $i->grabFromCurrentUrl();
$i->openPage($url . '&action=delete&confirm=1');
$i->openPage($hosting->urlOptions);
$hosting->submit();

$i->wantTo('Check cabinet');
$hosting->logout();
$ulogin->login();
$i->openPage($hosting->cabinetUrl);
$i->openPage($hosting->addUrl);
$i->fillField($hosting->title, $data->title);
$i->fillField($hosting->text, $data->text);
$hosting->screenshot('create');
$i->click($hosting->addButton);
$i->checkError();

$i->wantTo('Add message to ticket');
$i->fillField($hosting->message, $data->message);
$hosting->screenshot('addmessage');
$i->click($hosting->send);
$i->checkError();
$hosting->screenshot('messages');
$hosting->logout();
}
}