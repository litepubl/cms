<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

use Page\Admin;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test admin panel');
$admin = new Admin($i, '05admin');
$admin->open();

$list = $admin->getLinks('ajaxLinks');
foreach ($list as $url) {
    $i->wantTo("Test page $url");
    $i->openPage('/admin/' . $url);
}

$list = $admin->getLinks('adminForms');
foreach ($list as $url) {
    $i->wantTo("Test form $url");
    $i->openPage('/admin/' . $url);
    $admin->submit();
}

$list = $admin->getMenu();
foreach ($list as $j => $url) {
    //codecept_debug($url);
    $i->wantTo("Test page $url");
    $i->amOnUrl($url);
    $i->checkError();
    $admin->screenShot(str_replace('/', '-', trim($url, '/')));
    $admin->submit();
    $i->checkError();
}
