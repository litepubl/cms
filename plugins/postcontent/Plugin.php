<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\plugins\postcontent;

use litepubl\core\Event;

class Plugin extends \litepubl\core\Plugin
{

    protected function create()
    {
        parent::create();
        $this->data['before'] = '';
        $this->data['after'] = '';
    }

    public function beforeContent(Event $event)
    {
        $event->content = $this->before . $event->content;
    }

    public function aftercontent(Event $event)
    {
        $event->content.= $this->after;
    }
}
