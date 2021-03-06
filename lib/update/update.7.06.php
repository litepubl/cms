<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\update;

use litepubl\core\Plugins;
use litepubl\view\AutoVars;

function update706()
{
    $plugins = Plugins::i();
    if (isset($plugins->items['catbread'])) {
        AutoVars::i()->add('catbread', 'litepubl\plugins\catbread\CatBread');
    }
}
