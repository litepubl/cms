<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\rssfiles;

use litepubl\core\Event;
use litepubl\post\Post;

class Plugin extends \litepubl\core\Plugin
{

    public function beforePost(Event $event)
    {
        $post = Post::i($event->id);
        if (count($post->files) > 0) {
            $view = $post->getView();
            $theme = $view->theme;
            $image = $theme->templates['content.post.filelist.image'];
            $theme->templates['content.post.filelist.image'] = str_replace(
                'href="$link"',
                //photoSwipe template hash
                'href="$post.link#gid=$post.id&pid=$typeindex"',
                //old prettyPhoto template
                //'href="$post.link#!prettyPhoto[gallery-$post.id]/$typeindex/"',
                $image
            );

            $event->content.= $view->filelist;
            $theme->templates['content.post.filelist.image'] = $image;
        }
    }
}
