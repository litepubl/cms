<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class trssfilelist extends \litepubl\core\Plugin
 {

    public static function i() {
        return getinstance(__class__);
    }

    public function beforepost($id, &$content) {
        $post = tpost::i($id);
        if (count($post->files) > 0) {
            $theme = $post->theme;
            $image = $theme->templates['content.post.filelist.image'];
            $theme->templates['content.post.filelist.image'] = str_replace('href="$link"', 'href="$post.link#!prettyPhoto[gallery-$post.id]/$typeindex/"', $image);
            $content.= $post->filelist;
            $theme->templates['content.post.filelist.image'] = $image;
        }
    }

} 