<?php

namespace litepubl\tests\shop;

class S50PartnerCest extends \Page\Base
{
    protected $url = '/admin/shop/partners/';
    protected $outUrl = '/admin/shop/partners/outpays/';
protected $selectButton = 'button[name="select"]';
    protected $urlOptions = '/admin/shop/partners/options/';
protected $tabPays = '#tab-1';
    protected $promoUrl = '/admin/shop/partners/promo/';
protected $promoEditor = '#editor-promo';
protected $demoText = 'pre';
    protected $tariffUrl = '/admin/shop/partners/tariffs/';
protected $percent = '#text-percent';

    protected $regUrl = '/admin/regpartner/';
    protected $cabinetUrl = '/admin/cabinet/partner/';
protected $promoCabinet = '/admin/cabinet/partner/promo/';
protected $payAccount = '?id=payaccount';

    protected function test(\AcceptanceTester $i)
    {
$i->wantTo('Test install and uninstall support partner program in shop');
$data = $this->load('shop/partner');
$ulogin = $this->getUlogin();
$this ->ReInstallPlugin('partner', 160);

$i->openPage($this->url);
$i->openPage($this->outUrl);
$i->click($this->selectButton);
$i->checkError();

$i->wantTo('Check tariff options');
$i->openPage($this->tariffUrl);
$this->screenshot('tariffs');
$i->fillField($this->title, $data->title);
$i->fillField($this->percent, $data->percent);
$this->submit();
$this->screenshot('tariffs');
$i->click($data->title);
$i->checkError();

$i->wantTo('Check options page');
$i->openPage($this->urlOptions);
$this->screenshot('options');
$i->click($this->tabPays);
usleep(400000);
$this->screenshot('tabpays');
$this->submit();

$i->wantTo('Check promo page');
$i->openPage($this->promoUrl);
$this->screenshot('editpromo');
if ($this->exists($this->demoText)) {
$promo = $i->grabTextFrom($this->demoText);
$i->fillField($this->promoEditor, $promo);
}
$this->submit();
$this->screenshot('editpromo');

$i->wantTo('Check cabinet');
$this->logout();
$ulogin->_login();
$i->openPage($this->regUrl);
$i->openPage($this->cabinetUrl);
$i->openPage($this->cabinetUrl . $this->payAccount);
$this->submit();

$i->openPage($this->promoCabinet);
}
}