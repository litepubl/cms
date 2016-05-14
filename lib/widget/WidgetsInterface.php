<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\widget;

use ArrayObject;
use litepubl\core\Str;

interface WidgetsInterface {
    public function getWidgets(ArrayObject $items, $sidebar);
    public function getSidebar(Str $str, $sidebar);
}

