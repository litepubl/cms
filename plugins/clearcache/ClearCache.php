<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\clearcache;

use litepubl\view\Schemes;
use litepubl\view\Theme;

class ClearCache extends \litepubl\core\Plugin
{

    public function clearcache()
    {
        Theme::clearCache();
    }

    public function parsed(Theme $theme)
    {
        $name = $theme->name;
        $schemes = Schemes::i();
        foreach ($schemes->items as & $itemview) {
            if ($name == $itemview['themename']) {
                $itemview['custom'] = $theme->templates['custom'];
            }
        }
        $schemes->save();
    }
}
