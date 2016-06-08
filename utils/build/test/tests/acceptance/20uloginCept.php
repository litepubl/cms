<?php 

use Page\Plugin;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test ulogin plugin');
$plugin = new Plugin($i);
$plugin->install('ulogin');
$plugin->logout();
$i->wantTo('Send comment as authorized user');
$i->openPage('/');
$i->wantTo('Open first post');
$i->click('.post-bookmark');

$comment = new Comment($i);
$i->wantTo('Send anonimouse comment');
$comment->send($data->comment . time());
$i->wantTo('Confirm comment');
$i->screenShot('11confirm');

$i->click($data->human);
$i->checkError();

$i->wantTo('Send empty comment');
$comment->send('');
$i->screenShot('11error');
$i->see($data->error);
$i->wantTo('Close error dialog');
$i->click('Ok');

$i->wantTo('Send comment as admin');
$i->click($data->login);
codecept_debug($i->grabFromCurrentUrl());
Login::i($i)->login();

$i->wantTo('Must be returned back to post');
$i->seeCurrentUrlEquals($posturl);

$comment->send($data->comment2 . time());
$i->wantTo('Check comment sent');
$i->see($data->comment2);
