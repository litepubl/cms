<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\pages;

use litepubl\core\Context;
use litepubl\view\Theme;

class Appcache extends \litepubl\core\Items
{

    public function create()
    {
        parent::create();
        $this->basename = 'appcache.manifest';
        $this->dbversion = false;
        $this->data['url'] = '/manifest.appcache';
        $this->data['idurl'] = 0;
    }

    public function add($value)
    {
        if (!in_array($value, $this->items)) {
            $this->items[] = $value;
            $this->save();
            $this->getApp()->cache->clearUrl($this->url);
            $this->added($value);
        }
    }

    public function getText()
    {
        return implode("\r\n", $this->items);
    }

    public function setText($value)
    {
        $this->items = explode(
            "\n", trim(
                str_replace(
                    array(
                    "\r\n",
                    "\r"
                    ), "\n", $value
                )
            )
        );
        $this->save();
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->headers['Content-Type'] = 'text/cache-manifest';
        $response->body = "CACHE MANIFEST\r\n";
        $response->body.= Theme::i()->parse($this->text);
    }
}
