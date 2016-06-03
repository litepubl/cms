<?php 

use Page\RegUser;
use Page\Password;
use Page\Login;
use test\config;

$i = new AcceptanceTester($scenario);
$reguser = new RegUser($i);
//to grab confirm link and test restore password of new user
$password = new Password($i);

$i->wantTo('Enable user registration');
$reguser->open($reguser->optionsUrl);
$i->checkOption($reguser->enabled);
$i->checkOption($reguser->reguser);
$i->screenshot('04.01options');
$i->click($reguser->updateButton);
$i->checkError();

$reguser->logout();
$reguser->open();
$i->wantTo('Register new user');
$user = $reguser->load('reguser');
$user->email = time() . $user->email;
$i->fillField($reguser->email, $user->email);
$i->fillField($reguser->name, $user->email);
$i->screenshot('04.02reguser');
$password->removeLogs();
$i->click($reguser->submit);
$i->checkError();
$i->screenshot('04.03confirm');

$password->confirmEmail();
$i->screenshot('04.04confirmed');

$i->wantTo('Logon as new user');
$i->openPage('/admin/');
$i->checkError();
$i->screenshot('04.05logged');

$i->wantTo('Check restore passwordof new user');
$reguser->logout();

$password->removeLogs();
$i->wantTo('Open restore password page');
$i->openPage($password->url);

$login = Login::i($i);
$user->password = $password->restore($user->email);
//config::save('reguser', $user);

$i->wantTo('Login with new password');
$i->openPage($login->url);
$login->auth($user->email, $user->password);

$reguser->logout();