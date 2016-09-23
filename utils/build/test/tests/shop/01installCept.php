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
$i->wantTo('Test install and uninstall shop plugin');
$plugin = new Plugin($i, '101install');
$plugin->install('jslogger', 160);

if ($i->executeJs('return $("input[name=base]").prop("checked");')) {
    codecept_debug('Skip shop install');
    return;
}

$plugin->install('base', 160);
$plugin->uninstall('base');
$plugin->install('base', 160);
$plugin->install('real', 160);