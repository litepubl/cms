<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class TRSSPrevNext extends tplugin {

    public static function i() {
        return getinstance(__class__);
    }

    public function beforepost($id, &$content) {
        $post = tpost::i($id);
        $content.= $post->prevnext;
    }

} //class