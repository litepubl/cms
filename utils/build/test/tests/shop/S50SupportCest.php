<?php

namespace litepubl\tests\shop;

class S50SupportCest extends \Page\Base
{
    protected $url = '/admin/shop/tickets/';
    protected $urlOptions = '/admin/shop/tickets/options/';
    protected $urlCats = '/admin/shop/tickets/cats/';
    protected $catTitle = '#text-cattitle';
    protected $cabinetUrl = '/admin/cabinet/tickets/';
    protected $addUrl = '/admin/cabinet/tickets/?action=add';
    protected $cat = '#combo-cat';
    protected $text = '#editor-raw';
    protected $addButton = 'button[name="newticket"]';
    protected $message = '#editor-message';
    protected $send = 'button[name="sendmesg"]';

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test install and uninstall support system in shop');
        $data = $this->load('shop/support');
        $ulogin = $this->getUlogin();
        $this ->reInstallPlugin('support', 160);

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
        $ulogin->_login();
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
