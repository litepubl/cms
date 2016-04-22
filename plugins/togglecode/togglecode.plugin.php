<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Js;

class ttogglecode extends \litepubl\core\Plugin
 {

    public static function i() {
        return getinstance(__class__);
    }

    public function install() {
        Js::i()->add('default', $this->jsfile);
    }

    public function uninstall() {
        Js::i()->deletefile('default', $this->jsfile);
    }

    public function getJsfile() {
        return '/plugins/' . basename(dirname(__file__)) . '/togglecode.min.js';
    }

} 