<?php

use Page\Install;
use Page\Installed;
use test\config;

if (config::exists('admin')) {
codecept_debug('Install skiped');
return;
}

$i = new AcceptanceTester($scenario);
$page = new Install($i);
$page->removeData();
$i->wantTo('Open install form');
$i->openPage($page::$url);

if (config::$screenshot) {
$i->makeScreenshot('01installform');
}
//$page->switchLanguages();
$page->fillForm();

$installed = new Installed($i);
$installed->saveAccount();

if (config::$screenshot) {
$i->makeScreenshot('01installed');
}

$i->wantTo('Open login page');
$i->click($installed::$link);
$i->checkError();
//file_put_contents(__dir__ . '/url.txt', $i->grabFromCurrentUrl());
