<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\plugins\youtubeplayer;

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

    public function filter(&$content)
    {
        if (preg_match_all(
            "/\[youtube\=http:\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|\/v\/|#!v=)([a-zA-Z0-9\-\_]{11})([^<\s]*)\]/",
            //"/\[youtube\=http:\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|\/v\/)([a-zA-Z0-9\-\_]{11})([^<\s]*)\]/",
            $content,
            $m,
            PREG_SET_ORDER
        )) {
            foreach ($m as $item) {
                $id = $item[3];
                $content = str_replace($item[0], str_replace('$id', $id, $this->template), $content);
            }
        }

        if (preg_match_all('/http:\/\/youtu\.be\/(\w*+)/', $content, $m, PREG_SET_ORDER)) {
            foreach ($m as $item) {
                $id = $item[1];
                $content = str_replace($item[0], str_replace('$id', $id, $this->template), $content);
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
