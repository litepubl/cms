<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tsmiles extends \litepubl\core\Plugin
 {

    public static function i() {
        return getinstance(__class__);
    }

    public function filter(&$content) {
        $content = str_replace(array(
            ':)',
            ';)'
        ) , sprintf('<img src="%s/plugins/%s/1.gif" alt="smile" title="smile" />',  $this->getApp()->site->files, basename(dirname(__file__))) , $content);
    }

}