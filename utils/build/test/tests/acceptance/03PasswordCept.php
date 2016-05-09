<?php 

use Page\Password;
use test\config;
use litepubl\utils\Filer;

$i = new AcceptanceTester($scenario);
$password = new Password($i);
$password->logout();

//clear logs for find single fifile with email and link to restore
Filer::delete(config::$home . '/storage/data/logs/', false, false);
$i->wantTo('Open restore password page');
$i->openPage($password->url);

if (config::$screenshot) {
$i->makeScreenshot('03password');
}

$password->restore();

