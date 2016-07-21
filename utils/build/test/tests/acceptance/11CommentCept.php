<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

use Page\Comment;

$i = new AcceptanceTester($scenario);
$i->maximizeWindow();
$comment = new Comment($i, '11comment');
$comment->logout();
$data = $comment->load('comment');

$i->wantTo('Click post on home page');
$i->openPage('/');
$i->wantTo('Open first post');
$i->click($comment->postlink);
$posturl = $i->grabFromCurrentUrl();
$i->wantTo('Send anonimouse comment');
$comment->send($data->comment . time());
$i->wantTo('Confirm comment');
$i->waitForText($data->human, 3);
$comment->screenShot('confirm');
$i->click($data->human);
$i->checkError();

$i->wantTo('Send empty comment');
$comment->send('');
$i->see($data->error);
$comment->screenShot('emptyerror');

$i->wantTo('Close error dialog');
$i->click('Ok');

$i->wantTo('Send comment as admin');
$i->click($data->login);
$i->seeInCurrentUrl(urlencode($posturl));
$comment->login();

$i->wantTo('Must be returned back to post');
$i->seeCurrentUrlEquals($posturl);

$comment->send($data->comment2 . time());
$i->wantTo('Check comment sent');
$i->see($data->comment2);
