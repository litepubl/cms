<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\update;
use litepubl\core;

function updatePlugins()
{
$map = include (__DIR__ . '/pluginsmap.php');
$plugins = core\Plugins::i();
foreach ($plugins->items as $name => $item) {

if (isset($map[$name])) {
unset($plugins->items[$name]);
$plugins->items[$map[$name]] = $item;
}
}

$plugins->save();

if (isset($plugins->items['wiki'])) {
$vars = core\AutoVars::i();
$vars->items['wiki'] = 'litepubl\plugins\wikiwords\Wiki';
$vars->save();
}
}