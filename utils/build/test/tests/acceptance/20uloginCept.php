<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

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
$i->waitForJs('return (\'litepubl\' in window) && (\'authdialog\' in window.litepubl);', 7);
$i->wantTo('Open auth dialog');
$ulogin->screenshot('post');
$i->click($data->login);
$ulogin->click();
$ulogin->screenshot('dialog');
$ulogin->auth();
$ulogin->waitForcloseDialog();
$i->reloadPage();
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
//$i->openPage('/admin/login/');
$ulogin->screenshot('login');
$ulogin->click();
$i->waitForUrlChanged(10);
codecept_debug($i->grabFromCurrentUrl());
$ulogin->logout();
$plugin->uninstall('ulogin');
$ulogin->screenshot('uninstall');
