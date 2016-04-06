<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tclearcache extends tplugin {

    public static function i() {
        return getinstance(__class__);
    }

    public function clearcache() {
        tfiler::delete(litepubl::$paths->data . 'themes', false, false);
        litepubl::$urlmap->clearcache();
    }

    public function themeparsed(ttheme $theme) {
        $name = $theme->name;
        $views = tviews::i();
        foreach ($views->items as & $itemview) {
            if ($name == $itemview['themename']) {
                $itemview['custom'] = $theme->templates['custom'];
            }
        }
        $views->save();
    }

} //class