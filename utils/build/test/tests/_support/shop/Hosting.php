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
    protected $cabinetUrl = '/admin/cabinet/hosting/';
    protected $buyUrl = '/admin/cabinet/hosting/buy/';
    protected $cabinetPlugins = '/admin/cabinet/hosting/plugins/';
    protected $domainUrl = '/admin/cabinet/hosting/domain/';
    protected $createUrl = '/admin/cabinet/hosting/create/';

protected $cat = '#combo-cat';
protected $text = '#editor-raw';
protected $addButton = 'button[name="newticket"]';
protected $message = '#editor-message';
protected $send = 'button[name="sendmesg"]';

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
}

protected function testCabinet()
{

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
