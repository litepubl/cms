<?php 

use Page\Admin;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test admin panel');
$admin = new Admin($i);
$admin->open();

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