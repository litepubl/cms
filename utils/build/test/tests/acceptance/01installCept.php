<?php

use Page\Install;
use Page\Installed;
use litepubl\test\config;
use litepubl\utils\Filer;

codecept_debug('hi');
$i = new AcceptanceTester($scenario);
$i->wantTo('Remove data files');
require_once(config::$home . '/lib/utils/Filer.php');
Filer::delete(config::$home . '/storage/data', true, false);
$i->dontSeeFileExists(config::$home . '/storage/data/index.htm');

$page = new Install($i);
$i->wantTo('Open install form');
$i->openPage($page::$url);
$i->wantTo('Switch languages');
$page->changeLanguage('English');
$page->changeLanguage('Russian');
$page->fillForm();

$installed = new Installed($i);
$installed->saveAccount();

$i->wantTo('Open login page');
//$i->seeLink($installed::$link);
//$i->click($installed::$link);
//$i->clickLink($installed::$link);
//$i->executeJS('return document.getElementById("admin-login").click();');
//$v = $i->executeJS('return $("#admin-login").click().attr("href");');
//$v = $i->executeJS('location = $("#admin-login").click().attr("href");');
$i->click('See admin');
//$i->waitForJS('$("#admin-login").click();', 2);
//file_put_contents(__DIR__ . '/testlog.txt', $v);

$i->checkError();
file_put_contents(__dir__ . '/url.txt', $i->grabFromCurrentUrl());
//file_put_contents(__dir__ . '/link.txt', $installed::$link);