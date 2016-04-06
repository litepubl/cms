<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tsmiles extends tplugin {

    public static function i() {
        return getinstance(__class__);
    }

    public function filter(&$content) {
        $content = str_replace(array(
            ':)',
            ';)'
        ) , sprintf('<img src="%s/plugins/%s/1.gif" alt="smile" title="smile" />', litepubl::$site->files, basename(dirname(__file__))) , $content);
    }

}