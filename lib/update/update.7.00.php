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

use litepubl\core\Str;
use litepubl\core\litepubl;
use litepubl\view\Js;
use litepubl\view\Css;
use litepubl\view\Parser;
use litepubl\core\Plugins;
use litepubl\core\DBManager;

function update700()
{
    litepubl::$app->site->jquery_version = '1.12.4';
    $css = Css::i();
    $css->deletestyle("/plugins/regservices/regservices.min.css");

$plugins = Plugins::i();
if (isset($plugins->items['downloatitems'])) {
$js = Js::i();
$js->lock();
$js->replaceFile('default',
'/plugins/downloaditem/downloaditem.min.js',
'/plugins/downloaditem/resource/downloaditem.min.js'
);

$js->unlock();

$parser = Parser::i();
    $parser->unbind('tdownloaditems');
    $parser->addTags('plugins/downloaditem/resource/theme.txt', 'plugins/downloaditem/resource/theme.ini');

$man = DBManager::i();
if ($man->columnExists('downloaditems', 'votes')) {
$man->deleteColumn('downloaditems', 'votes');
}

if ($man->columnExists('downloaditems', 'poll')) {
$man->deleteColumn('downloaditems', 'poll');
}
}

if (count(litepubl::$app->classes->items)) {
include (__DIR__ . '/updateEvents.php');
updateEvents();

include (__DIR__ . '/updatePlugins.php');
updatePlugins();
}
}