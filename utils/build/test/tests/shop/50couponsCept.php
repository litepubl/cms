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
use shop\Coupons;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test install and uninstall couponsin shop');
$coupons = new Coupons($i, '150coupons');
$data = $coupons->load('shop/coupons');
$ulogin = new Ulogin($i, '150coupons');
$coupons ->installPlugin('coupons', 160);
$coupons ->uninstallPlugin('coupons');
$coupons ->installPlugin('coupons', 160);

$i->openPage($coupons->url);
$i->wantTo('Create new ');
$coupon = $i->grabValueFrom($coupons->value);
$i->fillField($coupons->title, $data->title);
$i->fillField($coupons->expired, date('d.m.Y', strtotime('+1 month')));
$coupons->screenshot('create');
$coupons->submit();
$i->wantTo('Edit spec condition');
$i->fillField($coupons->iddata, $data->iddata);
$coupons->screenshot('iddata');
$coupons->submit();

$i->wantTo('Check table link');
$i->click(['link' => $coupon]);
$i->checkError();



$coupons->logout();