<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\pages;

use litepubl\core\Context;

class RobotsTxt extends \litepubl\core\Items implements \litepubl\core\ResponsiveInterface
{

    public function create()
    {
        parent::create();
        $this->basename = 'robots.txt';
        $this->dbversion = false;
        $this->data['idurl'] = 0;
    }

    public function AddDisallow($url)
    {
        return $this->add("Disallow: $url");
    }

    public function add($value)
    {
        if (!in_array($value, $this->items)) {
            $this->items[] = $value;
            $this->save();
            $this->getApp()->cache->clearUrl('/robots.txt');
            $this->added($value);
        }
    }

    public function getText()
    {
        return implode("\n", $this->items);
    }

    public function setText($value)
    {
        $this->items = explode("\n", $value);
        $this->save();
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->headers['Content-Type'] = 'text/plain';
        $response->body = $this->text;
    }
}
