<?php

use Page\Install;
use Page\Installed;
use litepubl\test\config;
use litepubl\utils\Filer;

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