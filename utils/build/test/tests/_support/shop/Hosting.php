<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace shop;

class Hosting extends \Page\Base
{
protected $data;
protected $ulogin;
    protected $url = '/admin/shop/hosting/';
    protected $editUrl = '/admin/shop/hosting/edit/';
    protected $tariffsUrl = '/admin/shop/hosting/tariffs/';
    protected $plugins = '/admin/shop/hosting/plugins/';
    protected $serversUrl = '/admin/shop/hosting/servers/';
    protected $regdomainsUrl = '/admin/shop/hosting/regdomains/';
    protected $optionsUrl = '/admin/shop/hosting/options/';
    protected $runUrl = '/admin/shop/hosting/run/';
protected $runFixture = 'return \litepubl\core\litepubl::$app->options->version;';
    protected $updateUrl = '/admin/shop/hosting/update/';
    protected $releaseUrl = '/admin/shop/hosting/release/';
    protected $releaseOptionsUrl = '/admin/shop/hosting/release/?options=release';
protected $idRelease = '[name="releasesite"]';
protected $createRelease = '[name="createrelease"]';
protected $runEditor = '[name="script"]';
protected $addRegDomain = '[name="addregdomain"]';

protected $openShop = 'button[name="openshop"]';
protected $createShopButton = 'button[name="createshop"]';
protected $shopName = '[name="shopname"]';
    protected $cabinetUrl = '/admin/cabinet/hosting/';
    protected $buyUrl = '/admin/cabinet/hosting/buy/';
    protected $cabinetPlugins = '/admin/cabinet/hosting/plugins/';
    protected $domainUrl = '/admin/cabinet/hosting/domain/';
    protected $createUrl = '/admin/cabinet/hosting/create/';
protected $calcPrice = 'button[name="calcPrice"]';
protected $buyButton = 'button[name="paymethod"]';
protected $addServer = '[name="addserver"]';
protected $accfile = '#text-accfile';

public function tryTest(\AcceptanceTester $I)
{
$this->data = $this->load('shop/hosting');
parent::tryTest($I);
}

protected function addServer(string $type)
{
$i = $this->tester;
$i->openPage($this->serversUrl);
$this->selectCombo('server', $type);
$i->click($this->addServer );
return  $this->getIdFromUrl();
}

protected function setAccFile(string $filename)
{
$i = $this->tester;
$i->fillField($this->accfile, $filename);
$this->submit();
}

protected function addAccount(): int
{
$this->tester->openPage($this->editUrl);
$this->submit();
return $this->getIdFromUrl();
}

protected function testRun(int $id)
{
$i = $this->tester;
$i->openPage("$this->runUrl?id=$id");
$i->fillField($this->runEditor, $this->runFixture);
$this->submit(5);
$i->seeElement('pre');
}

protected function setIdRelease(int $id)
{
$i = $this->tester;
$i->openPage($this->releaseOptionsUrl );
$i->fillField($this->idRelease, $id);
$this->submit();
}

protected function createRelease()
{
$i = $this->tester;
$i->openPage($this->releaseUrl);
$i->click($this->createRelease);
$i->waitForElement('body', 120);
$i->checkError();
$i->dontSee('Error');
}

protected function setProps(\StdClass $obj)
{
$i = $this->tester;
$props = get_object_vars($obj);
foreach ($props as $name => $value) {
$i->fillField("input[name=\"$name\"]", $value);
}
}

protected function addTariff()
{
$i = $this->tester;
$i->wantTo('Add new tariff');
$i->openPage($this->tariffsUrl);
$this->setProps($this->data->tariff);
$this->submit();

$i->wantTo('Check created tariff');
$i->click($this->data->tariff->title);
sleep(1);
$i->checkError();
$i->seeElement('[name="title"]');
}

protected function createPlugin()
{
$i = $this->tester;
$i->wantTo('Create plugin');
$i->openPage($this->plugins);
$this->setProps($this->data->newplugin);
$this->submit();

$i->wantTo('Delete new plugin');
$i->openPage($this->plugins . ?id=' . $this->data->newplugin);->pluginname . '&action=delete&confirm=1'););
}

private function getDeleteSiteUrl()
{
$i = $this->tester;
$i->waitForElement('body');
$i->waitForJs('return "jQuery" in window', 4);
$link = $this->js('deletelink.js');
if ($link) {
$a = parse_url($link);
parse_str($a['query'], $q);
if (isset($q['id']) && ((int) $q['id'] > 1)) {
return $a['path'] . '?' . $a['query'];
}
}

return false;
}

protected function deleteAllSites()
{
$i = $this->tester;
$i->wantTo('delete all sites exclude release site');
$this->open($this->url);
while ($url = $this->getDeleteSiteUrl()) {
$i->openPage($url . '&confirm=1');
}
}

protected function createShop()
{
$i = $this->tester;
$i->wantTo('Check create shop');
$i->click($this->openShop);
$this->ulogin->_click();
        $i->waitForUrlChanged(10);
$i->checkError();
$i->waitForElement($this->shopName, 10);
$i->click($this->createShopButton);
$i->checkError();
}

protected function testCabinet()
{
$i = $this->tester;
$i->openPage($this->domainUrl);

$i->wantTo('Test plugins');
$i->openPage($this->cabinetPlugins);
        $i->checkOption("input[name=cbrf]");
$this->submit();

$i->wantTo('Buy');
$i->openPage($this->buyUrl );
$i->click($this->calcPrice);
$i->checkError();
$i->click($this->buyButton );
$i->checkError();
}
}