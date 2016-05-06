<?php 

use Page\Password;

$i = new AcceptanceTester($scenario);
$password = new Password($i);
$password->logout();
$i->wantTo('Open restore password page');
$i->openPage($password::$url);
$password->restore();
$i->seeCurrentUrlEquals('/admin/');
$password->logout();
$i->seeCurrentUrlEquals($password::$url);