<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

use Page\Plugin;
use Page\Ulogin;
use shop\Partner;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test install and uninstall support partner program in shop');
$partner = new Partner($i, '150partner');
$data = $partner->load('shop/partner');
$ulogin = new Ulogin($i, '150partner');
$plugin = new Plugin($i, '150partner');
$plugin->install('partner', 160);
$plugin->uninstall('partner');
$plugin->install('partner', 160);

$i->openPage($partner->url);
$i->openPage($partner->outUrl);
$i->click($partner->selectButton);
$i->checkError();

$i->wantTo('Check tariff options');
$i->openPage($partner->tariffUrl);
$partner->screenshot('tariffs');
$i->fillField($partner->title, $data->title);
$i->fillField($partner->percent, $data->percent);
$partner->submit();
$partner->screenshot('tariffs');
$i->click($data->title);
$i->checkError();

$i->wantTo('Check options page');
$i->openPage($partner->urlOptions);
$partner->screenshot('options');
$i->click($partner->tabPays);
usleep(400000);
$partner->screenshot('tabpays');
$partner->submit();

$i->wantTo('Check promo page');
$i->openPage($partner->promoUrl);
$partner->screenshot('editpromo');
$promo = $i->grabTextFrom($partner->demoText);
$i->fillField($partner->promoEditor, $promo);
$partner->submit();
$partner->screenshot('editpromo');

return;
$i->wantTo('Check cabinet');
$partner->logout();
$ulogin->login();
$i->openPage($partner->cabinetUrl);
$i->openPage($partner->addUrl);
$i->fillField($partner->title, $data->title);
$i->fillField($partner->text, $data->text);
$partner->screenshot('create');
$i->click($partner->addButton);
$i->checkError();

$i->wantTo('Add message to ticket');
$i->fillField($partner->message, $data->message);
$partner->screenshot('addmessage');
$i->click($partner->send);
$i->checkError();
$partner->screenshot('messages');