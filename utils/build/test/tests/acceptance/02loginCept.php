<?php 

use Page\Login;

$i = new AcceptanceTester($scenario);
$login = new Login($i);
$login->open();
$login->login();
$i->seeCurrentUrlEquals('/admin/');
$login->logout();
$i->seeCurrentUrlEquals($login::$url);