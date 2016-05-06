<?php

use Page\Install;
use Page\Installed;
use litepubl\test\config;

if (config::exists('admin')) {
codecept_debug('Install skiped');
return;
}

$i = new AcceptanceTester($scenario);
$page = new Install($i);
$page->removeData();
$i->wantTo('Open install form');
$i->openPage($page::$url);

//$page->switchLanguages();
$page->fillForm();

$installed = new Installed($i);
$installed->saveAccount();
$i->wantTo('Open login page');
$i->click($installed::$link);
$i->checkError();
//file_put_contents(__dir__ . '/url.txt', $i->grabFromCurrentUrl());
