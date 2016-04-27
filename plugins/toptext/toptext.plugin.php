<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class ttoptext extends \litepubl\core\Plugin
 {
    public $text;

    public static function i() {
        return getinstance(__class__);
    }

    public function beforecontent(tpost $post, &$content, &$cancel) {
        $sign = '[toptext]';
        if ($i = strpos($content, $sign)) {
            $this->text = substr($content, 0, $i);
            $content = substr($content, $i + strlen($sign));
        }
    }

    public function aftercontent(tpost $post) {
        if ($this->text) $post->filtered = $this->text . $post->filtered;
    }

}