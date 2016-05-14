<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\oldestposts;

use litepubl\widget\Widgets;

function OldestpostsInstall($self)
{
    $widgets = Widgets::i();
    $widgets->addclass($self, '\litepubl\post\Post');
}

