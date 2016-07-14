<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\markdown;

use litepubl\core\Event;

class Plugin extends \litepubl\core\Plugin
{
    public $parser;

    protected function create()
    {
        parent::create();
        $this->data['deletep'] = false;
        include_once __DIR__ . '/MarkdownInterface.php';
        include_once __DIR__ . '/Markdown.php';
        $this->parser = new \Michelf\Markdown();
    }

    public function filter(Event $event)
    {
        if ($this->deletep) {
            $event->content = str_replace('_', '&#95;', $event->content);
        }
        $event->content = $this->parser->transform($event->content);
        if ($this->deletep) {
            $event->content = strtr($event->content, array(
                '<p>' => '',
                '</p>' => '',
                '&#95;' => '_'
                )            );
        }
    }
}
