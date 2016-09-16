<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

use Page\Admin;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test shop admin panel');
$admin = new Admin($i, '103menu');
$admin->open('/admin/shop/');

$list = $admin->getLinks('shop/forms');
foreach ($list as $url) {
    $i->wantTo("Test form $url");
    $i->openPage('/admin/' . $url);
    $admin->submit();
}

$admin->open('/admin/shop/');
$list = $admin->getMenu();
foreach ($list as $j => $url) {
    //codecept_debug($url);
    $i->wantTo("Test page $url");
    $i->amOnUrl($url);
    $i->checkError();
$i->waitForElement('body', 6);
    $admin->screenShot(str_replace('/', '-', trim($url, '/')));
    $admin->submit();
    $i->checkError();
$i->waitForElement('body', 6);
}

$admin->open('/admin/cabinet/');
$list = $admin->getMenu();
foreach ($list as $j => $url) {
    //codecept_debug($url);
    $i->wantTo("Test page $url");
    $i->amOnUrl($url);
    $i->checkError();
$i->waitForElement('body', 6);
    $admin->screenShot(str_replace('/', '-', trim($url, '/')));
    $admin->submit();
    $i->checkError();
$i->waitForElement('body', 6);
}
