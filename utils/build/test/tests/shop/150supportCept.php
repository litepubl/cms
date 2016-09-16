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
$i->wantTo('Test install and uninstall support system in shop');
$plugin = new Plugin($i, '150support');
$plugin->install('support', 160);
$plugin->uninstall('support');
$plugin->install('support', 160);
