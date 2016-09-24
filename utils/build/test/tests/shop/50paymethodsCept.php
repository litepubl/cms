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

$i = new AcceptanceTester($scenario);
$i->wantTo('Test install and uninstall yandex market plugin');
$plugin = new Plugin($i, '150paymethods');
foreach (['qiwi', 'robokassa', 'webmoney', 'yandexmoney'] as $name) {
$plugin->install($name, 160);
$plugin->uninstall($name);
$plugin->install($name, 160);
}