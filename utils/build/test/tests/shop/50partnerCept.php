<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

use Page\Ulogin;
use shop\Partner;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test install and uninstall support partner program in shop');
$partner = new Partner($i, '150partner');
$data = $partner->load('shop/partner');
$ulogin = new Ulogin($i, '150partner');
$partner ->installPlugin('partner', 160);
$partner ->uninstallPlugin('partner');
$partner ->installPlugin('partner', 160);

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
if ($partner->exists($partner->demoText)) {
$promo = $i->grabTextFrom($partner->demoText);
$i->fillField($partner->promoEditor, $promo);
}
$partner->submit();
$partner->screenshot('editpromo');

$i->wantTo('Check cabinet');
$partner->logout();
$ulogin->_login();
$i->openPage($partner->regUrl);
$i->openPage($partner->cabinetUrl);
$i->openPage($partner->cabinetUrl . $partner->payAccount);
$partner->submit();

$i->openPage($partner->promoCabinet);