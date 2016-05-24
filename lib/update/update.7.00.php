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

function update700()
{
    litepubl::$app->site->jquery_version = '1.12.4';
    $css = Css::i();
    $css->deletestyle("/plugins/regservices/regservices.min.css");


if (count(litepubl::$app->classes->items)) {
include (__DIR__ . '/updateEvents.php');
updateEvents();

include (__DIR__ . '/updatePlugins.php');
updatePlugins();
}
}