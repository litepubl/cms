<?php 

use Page\Admin;
use Page\Login;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test admin panel');
$admin = new Admin($i);
$i->openPage($admin::$url);
$i->maximizeWindow();
if ($admin::$url != $i->grabFromCurrentUrl()) {
$login = new Login($i);
$login->login();
}

$i->seeCurrentUrlEquals('/admin/');

$list = $admin->getPages();
foreach ($list as $url) {
$i->wantTo("Test page $url");
$i->openPage('/admin/' . $url);
}

$list = $admin->getAjax();
foreach ($list as $url) {
$i->wantTo("Test page $url");
$i->openPage('/admin/' . $url);
}

$list = $admin->getForms();
foreach ($list as $url) {
$i->wantTo("Test form $url");
$i->openPage('/admin/' . $url);
$admin->submit();
}

$list = $admin->getMenu();
foreach ($list as $url) {
//codecept_debug($url);
$i->wantTo("Test page $url");
$i->amOnUrl($url);
$i->checkError();
$admin->submit();
$i->checkError();
}