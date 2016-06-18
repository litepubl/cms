<?php

use Page\Login;
use test\config;

$i = new AcceptanceTester($scenario);
$login = Login::i($i);
$i->wantTo('Open login page');
$i->openPage($login->url);
$i->screenShot('02.01login');

$login->login();
$i->seeCurrentUrlEquals('/admin/');
$i->screenShot('02.03board');

$login->logout();
$i->seeCurrentUrlEquals($login->url);
