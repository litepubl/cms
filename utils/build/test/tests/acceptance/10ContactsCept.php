<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

use Page\Contacts;
use test\config;

$i = new AcceptanceTester($scenario);
$i->maximizeWindow();
$contacts = new Contacts($i);
$data = config::load('contacts');

$i->wantTo('Click contacts on home page');
$i->openPage('/');
$i->click($data->title);
$i->screenShot('10contacts');
$i->wantTo('Send form');
$contacts->sendForm($data->email, $data->message);
$i->wantTo('Send form with empty email');
$contacts->sendForm('', $data->message);
$i->wantTo('Send form without message');
$contacts->sendForm($data->email, '');
