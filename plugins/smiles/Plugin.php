<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\smiles;

use litepubl\core\Event;

class Plugin extends \litepubl\core\Plugin
{

    public function filter(Event $event)
    {
        $event->content = strtr(
            $event->content, [
            ':)' => $this->smile,
            ';)' => $this->smile,
            ':(' => $this->sad,
            ';(' => $this->sad,
            ]
        );
    }
}
