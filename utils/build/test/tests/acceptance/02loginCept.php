<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


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
