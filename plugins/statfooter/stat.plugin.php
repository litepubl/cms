<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tstatfooter extends \litepubl\core\Plugin
 {

    public static function i() {
        return getinstance(__class__);
    }

    public function getFooter() {
        return ' | <?php echo round(memory_get_usage()/1024/1024, 2), \'MB | \';' .
        //' echo round(memory_get_peak_usage(true)/1024/1024, 2), \'MB | \';' .
        ' echo round(microtime(true) -  $this->getApp()->microtime, 2), \'Sec \'; ?>';
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