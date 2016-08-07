<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

use Page\Plugin;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test install and uninstall shop plugin');
$plugin = new Plugin($i, '101install');
$plugin->install('jslogger', 160);
$plugin->install('shop', 160);
$plugin->uninstall('shop');
$plugin->install('shop', 160);