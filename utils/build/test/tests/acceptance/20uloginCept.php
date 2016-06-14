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
$plugin->install('ulogin');
$plugin->logout();

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
$text = $data->comment . time();
$comment->send($text);
$i->wantTo('Check comment sent');
$i->waitForText($text, 6);

$i->wantTo('test ulogin without dialog box');
$ulogin->logout();
$i->openPage('/admin/login/');
$ulogin->click();
//dont need to wait auth because mailru remember prev auth
//$ulogin->auth();
//$i->savehtml('logged');
$i->waitForJS('return !litepubl || !litepubl.authdialog || litepubl.authdialog.ulogin.status == \'wait\';', 6);
$ulogin->logout();


$plugin->uninstall('ulogin');