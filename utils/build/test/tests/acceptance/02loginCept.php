<?php 

use Page\Login;
use test\config;

$i = new AcceptanceTester($scenario);
$login = new Login($i);
$login->open();

if (config::$screenshot) {
$i->makeScreenshot('02login');
}

$login->login();
$i->seeCurrentUrlEquals('/admin/');

if (config::$screenshot) {
$i->makeScreenshot('02board');
}

$login->logout();
$i->seeCurrentUrlEquals($login::$url);