<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

use shop\BuyPage;
use Page\Ulogin;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test purchase');
$buypage = new BuyPage($i, '140buypage');
$ulogin = new Ulogin($i, $buypage->screenshotName );
$buypage->logout();
$data = $buypage->load('shop/buypage');
$i->openPage('/');
$i->wantTo('Open first product');
$i->click($buypage->productLink);
$i->waitForJs('return (\'litepubl\' in window) && (\'authdialog\' in window.litepubl);', 7);
$buypage->screenshot('product');

$i->wantTo('Got to buy page');
$currentUrl = $i->grabFromCurrentUrl();
$i->click($buypage->cashButton);
sleep(2);
if ($currentUrl == $i->grabFromCurrentUrl()) {
$ulogin->click();
$buypage->screenshot('dialog');
$ulogin->auth();
$i->waitForUrlChanged(10);
}

if ($buypage->exists($buypage->editAddrButton)) {
$i->click($buypage->editAddrButton);
$i->checkError();
}

if ($buypage->isAddrEdit()) {
$buypage->fillAddress($data->addr);
$buypage->submit();
$i->checkError();
}

if ($buypage->exists($buypage->noteEditor)) {
//$i->executeJs("\$('$buypage->noteEditor').val('$data->note');");
$i->fillField($buypage->noteEditor, $data->note);
}

if ($buypage->exists($buypage->continueButton)) {
$i->click($buypage->continueButton);
$i->checkError();
$i->click($buypage->backButton);
$i->checkError();
$i->click($buypage->continueButton);
$i->checkError();
}

if ($buypage->exists($buypage->cashButton)) {
$i->click($buypage->cashButton);
$i->checkError();
}

$i->click($buypage->detailsButton);
$i->checkError();
