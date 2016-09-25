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
use shop\Hosting;
return;
$i = new AcceptanceTester($scenario);
$i->wantTo('Test install and uninstall hosting');
$hosting = new Hosting($i, '170hosting');
$data = $hosting->load('shop/hosting');
$ulogin = new Ulogin($i, '150hosting');
$plugin = new Plugin($i, '150hosting');
$plugin->install('hosting', 160);
$plugin->uninstall('hosting');
$plugin->install('hosting', 160);

$i->openPage($hosting->url);
$i->wantTo('Create new category');
$i->openPage($hosting->urlCats);
$i->fillField($hosting->catTitle, $data->cattitle);
$hosting->screenshot('addcat');
$hosting->submit();
$hosting->screenshot('added');
$i->click($data->cattitle);
$i->checkError();
$i->wantTo('Delete new catalog');
$url = $i->grabFromCurrentUrl();
$i->openPage($url . '&action=delete&confirm=1');
$i->openPage($hosting->urlOptions);
$hosting->submit();

$i->wantTo('Check cabinet');
$hosting->logout();
$ulogin->login();
$i->openPage($hosting->cabinetUrl);
$i->openPage($hosting->addUrl);
$i->fillField($hosting->title, $data->title);
$i->fillField($hosting->text, $data->text);
$hosting->screenshot('create');
$i->click($hosting->addButton);
$i->checkError();

$i->wantTo('Add message to ticket');
$i->fillField($hosting->message, $data->message);
$hosting->screenshot('addmessage');
$i->click($hosting->send);
$i->checkError();
$hosting->screenshot('messages');
$hosting->logout();