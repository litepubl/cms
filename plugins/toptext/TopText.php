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

use litepubl\core\Event;
use litepubl\post\Post;

class TopText extends \litepubl\core\Plugin
{
    public $text;

    public function beforeContent(Event $event)
    {
        $sign = '[toptext]';
        if ($i = strpos($event->content, $sign)) {
            $this->text = substr($event->content, 0, $i);
            $event->content = substr($event->content, $i + strlen($sign));
        }
    }

    public function afterContent(Event $event)
    {
        if ($this->text) {
            $event->post->filtered = $this->text . $event->post->filtered;
        }
    }
}
