<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

use Page\;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test install and uninstall shopping cart');
$plugin = new Plugin($i, '150shoppingcart');
$plugin->install('shoppingcart', 160);
$plugin->uninstall('shoppingcart');
$plugin->install('shoppingcart', 160);
