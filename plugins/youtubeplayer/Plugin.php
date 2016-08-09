<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\plugins\youtubeplayer;

use litepubl\core\Event;
use litepubl\view\Filter;

class Plugin extends \litepubl\core\Plugin
{

    protected function create()
    {
        parent::create();
        $this->data['template'] = '<object width="425" height="350">' . '<param name="movie" value="http://www.youtube.com/v/$id?fs=1&amp;rel=0"></param>' .
        //'<param name="wmode" value="transparent"></param>' .
        '<param name="allowFullScreen" value="true"></param>' . '<param name="allowscriptaccess" value="always"></param>' . '<embed src="http://www.youtube.com/v/$id?fs=1&amp;rel=0" ' . 'type="application/x-shockwave-flash" ' .
        //'wmode="transparent" ' .
        'allowscriptaccess="always" ' . 'allowfullscreen="true" ' . 'width="425" height="350">' . '</embed></object>';
    }

    public function filter(Event $event)
    {
        if (preg_match_all(
            "/\[youtube\=http:\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|\/v\/|#!v=)([a-zA-Z0-9\-\_]{11})([^<\s]*)\]/",
            //"/\[youtube\=http:\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|\/v\/)([a-zA-Z0-9\-\_]{11})([^<\s]*)\]/",
            $event->content,
            $m,
            PREG_SET_ORDER
        )) {
            foreach ($m as $item) {
                $id = $item[3];
                $event->content = str_replace($item[0], str_replace('$id', $id, $this->template), $event->content);
            }
        }

        if (preg_match_all('/http:\/\/youtu\.be\/(\w*+)/', $event->content, $m, PREG_SET_ORDER)) {
            foreach ($m as $item) {
                $id = $item[1];
                $event->content = str_replace($item[0], str_replace('$id', $id, $this->template), $event->content);
            }
        }
    }

    public function install()
    {
        Filter::i()->afterfilter = $this->filter;
    }

    public function uninstall()
    {
        Filter::i()->unbind($this);
    }
}
