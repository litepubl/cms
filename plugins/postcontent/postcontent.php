<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tpostcontentplugin extends \litepubl\core\Plugin
 {

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->data['before'] = '';
        $this->data['after'] = '';
    }

    public function beforecontent($post, &$content) {
        $content = $this->before . $content;
    }

    public function aftercontent($post, &$content) {
        $content.= $this->after;
    }

}