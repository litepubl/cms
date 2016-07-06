<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\toptext;

use litepubl\post\Post;

class TopText extends \litepubl\core\Plugin
{
    public $text;

    public function beforeContent(Post $post, &$content, &$cancel)
    {
        $sign = '[toptext]';
        if ($i = strpos($content, $sign)) {
            $this->text = substr($content, 0, $i);
            $content = substr($content, $i + strlen($sign));
        }
    }

    public function afterContent(Post $post)
    {
        if ($this->text) {
            $post->filtered = $this->text . $post->filtered;
        }
    }
}
