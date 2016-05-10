<?php 

use Page\Login;
use test\config;

$i = new AcceptanceTester($scenario);
$login = Login::i($i);

$i->wantTo('Open login page');
$i->openPage($login->url);

if (config::$screenshot) {
$i->makeScreenshot('02login');
}

$login->login();
$i->seeCurrentUrlEquals('/admin/');

if (config::$screenshot) {
$i->makeScreenshot('02board');
}

$login->logout();
$i->seeCurrentUrlEquals($login->url);