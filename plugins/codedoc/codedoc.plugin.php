<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tcodedocplugin extends \litepubl\core\Plugin
 {
    private $post;

    public static function i() {
        return static::iGet(__class__);
    }

    public function filterpost($post, &$content, &$cancel) {
        if (preg_match('/^(classname|interface)\s*[=:]\s*\w*+/i', $content, $m)) {
            $this->post = $post;
            $filter = tcodedocfilter::i();
            $content = $filter->filter($post, $content, $m[1]);
            $cancel = true;
        }
    }

    public function afterfilter($post, &$content, &$cancel) {
        if ($post == $this->post) {
            $post->filtered = $content;
            $cancel = true;
        }
    }

    public function postdeleted($id) {
         $this->getApp()->db->table = 'codedoc';
         $this->getApp()->db->delete("id = $id");
    }
}