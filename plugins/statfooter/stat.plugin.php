<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tstatfooter extends tplugin {

    public static function i() {
        return getinstance(__class__);
    }

    public function getfooter() {
        return ' | <?php echo round(memory_get_usage()/1024/1024, 2), \'MB | \';' .
        //' echo round(memory_get_peak_usage(true)/1024/1024, 2), \'MB | \';' .
        ' echo round(microtime(true) - litepubl::$microtime, 2), \'Sec \'; ?>';
    }

    public function install() {
        $footer = $this->getfooter();
        $template = ttemplate::i();
        if (!strpos($template->footer, $footer)) {
            $template->footer.= $footer;
            $template->save();
        }
    }

    public function uninstall() {
        $footer = $this->getfooter();
        $template = ttemplate::i();
        $template->footer = str_replace($footer, '', $template->footer);
        $template->save();
    }

} //class