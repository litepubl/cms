<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\pages;

use litepubl\core\Str;
use litepubl\view\MainView;

class Redirector extends \litepubl\core\Items
{

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'redirector';
        $this->addevents('onget');
    }

    public function add($from, $to)
    {
        $this->items[$from] = $to;
        $this->save();
        $this->added($from);
    }

    public function get($url)
    {
        if (isset($this->items[$url])) {
            return $this->items[$url];
        }

        if (Str::begin($url, $this->getApp()->site->url)) {
            return substr($url, strlen($this->getApp()->site->url));
        }

        //redir jquery scripts
        if (Str::begin($url, '/js/jquery/jquery')) {
            return '/js/jquery/jquery-' . $this->getApp()->site->jquery_version . '.min.js';
        }

        //fix for 2.xx versions
        if (preg_match('/^\/comments\/(\d*?)\/?$/', $url, $m)) {
            return sprintf('/comments/%d.xml', $m[1]);
        }

        if (preg_match('/^\/authors\/(\d*?)\/?$/', $url, $m)) {
            return '/comusers.htm?id=' . $m[1];
        }

        if (strpos($url, '%')) {
            $url = rawurldecode($url);
            if (Str::begin($url, $this->getApp()->site->url)) {
                return substr($url, strlen($this->getApp()->site->url));
            }

            if ($this->getApp()->router->urlexists($url)) {
                return $url;
            }
        }

        //fix php warnings e.g. function.preg-split
        if (($i = strrpos($url, '/')) && Str::begin(substr($url, $i), '/function.')) {
            return substr($url, 0, $i + 1);
        }

        //redir version js files
        if (preg_match('/^\/files\/js\/(\w*+)\.(\d*+)\.js$/', $url, $m)) {
            $name = $m[1] == 'moderator' ? 'comments' : $m[1];
            $prop = 'jsmerger_' . $name;
            $schema = MainView::i();
            if (isset($schema->$prop)) {
                return $schema->$prop;
            }
        }

        if (preg_match('/^\/files\/js\/(\w*+)\.(\d*+)\.css$/', $url, $m)) {
            $name = 'cssmerger_' . $m[1];
            $schema = MainView::i();
            if (isset($schema->$name)) {
                return $schema->$name;
            }
        }

        if ($url = $this->onget($url)) {
            return $url;
        }

        return false;
    }
}
