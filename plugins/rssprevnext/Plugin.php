<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\rssprevnext;

use litepubl\post\Post;

class Plugin extends \litepubl\core\Plugin
{

    public function beforePost($id, &$content)
    {
        $post = Post::i($id);
        $content.= $post->getView()->prevnext;
    }

}
