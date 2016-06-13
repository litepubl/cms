<?php 

use Page\Plugin;
use Page\Ulogin;
use page\Comment;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test ulogin plugin');
$ulogin = new Ulogin($i);
$comment = new Comment($i);
$data = $comment->load('comment');
$plugin = new Plugin($i);
//$plugin->install('ulogin');
//$plugin->logout();

$i->wantTo('Send comment as authorized user');
$i->openPage('/');
$i->wantTo('Open first post');
$i->click($comment->postlink);
$i->wantTo('Open auth dialog');
$i->click($data->login);
$ulogin->click();
$i->screenshot('20ulogin.22dialog');
$ulogin->auth();
$ulogin->waitForcloseDialog();
$comment->send($data->comment . time());
$i->wantTo('Check comment sent');
$i->see($data->comment2);

$i->wantTo('test ulogin without dialog box');
$ulogin->logout();
$i->openPage('/admin/login/');
$ulogin->click();
$ulogin->auth();
sleep(3);
$i->savehtml('logged');
$ulogin->logout();


$plugin->uninstall('ulogin');