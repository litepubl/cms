<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\widget;

use ArrayObject;
use litepubl\core\Str;

interface WidgetsInterface
{
    public function getWidgets(ArrayObject $items, int $sidebar);
    public function getSidebar(Str $str, int $sidebar);
}
