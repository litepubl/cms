<?php

use Page\Plugin;
use Page\Ulogin;
use page\Comment;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test ulogin plugin');
$ulogin = new Ulogin($i, '20ulogin');
$comment = new Comment($i);
$data = $comment->load('comment');
$plugin = new Plugin($i);
$plugin->install('ulogin');
$ulogin->screenshot('install');
$plugin->logout();

$i->wantTo('Send comment as authorized user');
$i->openPage('/');
$i->wantTo('Open first post');
$i->click($comment->postlink);
$i->wantTo('Open auth dialog');
$ulogin->screenshot('post');
$i->click($data->login);
$ulogin->click();
$ulogin->screenshot('dialog');
$ulogin->auth();
$ulogin->waitForcloseDialog();
$text = $data->comment . time();
$i->fillField($comment->comment, $text);
$ulogin->screenshot('comment');
$i->click($comment->submit);
$i->checkError();
$i->wantTo('Check comment sent');
$i->waitForText($text, 6);
$ulogin->screenshot('comment');
$i->wantTo('test ulogin without dialog box');
$ulogin->logout();
$i->openPage('/admin/login/');
$ulogin->screenshot('login');
$ulogin->click();
//dont need to wait auth because mailru remember prev auth
//$ulogin->auth();
//$i->savehtml('logged');
$i->waitForJS('return !litepubl || !litepubl.authdialog || litepubl.authdialog.ulogin.status == \'wait\';', 6);
$ulogin->logout();

$plugin->uninstall('ulogin');
$ulogin->screenshot('uninstall');