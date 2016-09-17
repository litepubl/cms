<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

use Page\Plugin;
use Page\Ulogin;
use shop\Support;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test install and uninstall support system in shop');
$support = new Support($i, '150support');
$data = $support->load('shop/support');
$ulogin = new Ulogin($i, '150support');
$plugin = new Plugin($i, '150support');
$plugin->install('support', 160);
$plugin->uninstall('support');
$plugin->install('support', 160);

$i->openPage($support->url);
$i->wantTo('Create new category');
$i->openPage($support->urlCats);
$i->fillField($support->catTitle, $data->cattitle);
$support->screenshot('addcat');
$support->submit();
$support->screenshot('added');
$i->click($data->cattitle);
$i->checkError();
$i->wantTo('Delete new catalog');
$url = $i->grabFromCurrentUrl();
$i->openPage($url . '&action=delete&confirm=1');
$i->openPage($support->urlOptions);
$support->submit();

$i->wantTo('Check cabinet');
$support->logout();
$ulogin->login();
$i->openPage($support->cabinetUrl);
$i->openPage($support->addUrl);
$i->fillField($support->title, $data->title);
$i->fillField($support->text, $data->text);
$support->screenshot('create');
$i->click($support->addButton);
$i->checkError();

$i->wantTo('Add message to ticket');
$i->fillField($support->message, $data->message);
$support->screenshot('addmessage');
$i->click($support->send);
$i->checkError();
$support->screenshot('messages');