<?php

use Page\Password;
use Page\Login;
use test\config;

$i = new AcceptanceTester($scenario);
$password = new Password($i);
$password->logout();
$password->removeLogs();

$i->wantTo('Open restore password page');
$i->openPage($password->url);
$i->screenShot('03.01password');

$login = Login::i($i);
$admin = $login->getAdmin();
$admin->password = $password->restore($admin->email);
config::save('admin', $admin);
$i->screenShot('03.02restored');

$i->wantTo('Login with new password');
$i->openPage($login->url);
$login->auth($admin->email, $admin->password);
