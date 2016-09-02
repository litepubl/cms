<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

use Page\Ulogin;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test purchase');
$ulogin = new Ulogin($i, '140buy');
$ulogin->logout();
$data = $comment->load('comment');
$i->openPage('/');
$i->wantTo('Open first product');
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
sleep(2);
codecept_debug($i->grabFromCurrentUrl());
$ulogin->screenshot('login');
$ulogin->click();
$i->waitForUrlChanged(10);
codecept_debug($i->grabFromCurrentUrl());
$ulogin->logout();
$plugin->uninstall('ulogin');
$ulogin->screenshot('uninstall');
