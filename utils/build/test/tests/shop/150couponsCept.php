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
use shop\Coupons;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test install and uninstall couponsin shop');
$coupons = new Coupons($i, '150coupons');
$data = $coupons->load('shop/coupons');
$ulogin = new Ulogin($i, '150coupons');
$plugin = new Plugin($i, '150coupons');
$plugin->install('coupons', 160);
$plugin->uninstall('coupons');
$plugin->install('coupons', 160);

$i->openPage($coupons->url);
$i->wantTo('Create new ');
$i->fillFild($coupons->title, $data->title);

$i->openPage($coupons->urlCats);
$i->fillField($coupons->catTitle, $data->cattitle);
$coupons->screenshot('addcat');
$coupons->submit();
$coupons->screenshot('added');
$i->click($data->cattitle);
$i->checkError();
$i->wantTo('Delete new catalog');
$url = $i->grabFromCurrentUrl();
$i->openPage($url . '&action=delete&confirm=1');
$i->openPage($coupons->urlOptions);
$coupons->submit();

$i->wantTo('Check cabinet');
$coupons->logout();
$ulogin->login();
$i->openPage($coupons->cabinetUrl);
$i->openPage($coupons->addUrl);
$i->fillField($coupons->title, $data->title);
$i->fillField($coupons->text, $data->text);
$coupons->screenshot('create');
$i->click($coupons->addButton);
$i->checkError();

$i->wantTo('Add message to ticket');
$i->fillField($coupons->message, $data->message);
$coupons->screenshot('addmessage');
$i->click($coupons->send);
$i->checkError();
$coupons->screenshot('messages');