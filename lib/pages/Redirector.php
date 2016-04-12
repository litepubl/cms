<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;
use litepubl\view\MainView;

class Redirector extends \litepubl\core\Items
 {

    protected function create() {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'redirector';
        $this->addevents('onget');
    }

    public function add($from, $to) {
        $this->items[$from] = $to;
        $this->save();
        $this->added($from);
    }

    public function get($url) {
        if (isset($this->items[$url])) return $this->items[$url];
        if (strbegin($url, litepubl::$site->url)) return substr($url, strlen(litepubl::$site->url));

        //redir jquery scripts
        if (strbegin($url, '/js/jquery/jquery')) return '/js/jquery/jquery-' . litepubl::$site->jquery_version . '.min.js';

        //fix for 2.xx versions
        if (preg_match('/^\/comments\/(\d*?)\/?$/', $url, $m)) return sprintf('/comments/%d.xml', $m[1]);
        if (preg_match('/^\/authors\/(\d*?)\/?$/', $url, $m)) return '/comusers.htm?id=' . $m[1];

        if (strpos($url, '%')) {
            $url = rawurldecode($url);
            if (strbegin($url, litepubl::$site->url)) return substr($url, strlen(litepubl::$site->url));
            if (litepubl::$urlmap->urlexists($url)) return $url;
        }

        //fix php warnings e.g. function.preg-split
        if (($i = strrpos($url, '/')) && strbegin(substr($url, $i) , '/function.')) {
            return substr($url, 0, $i + 1);
        }

        //redir version js files
        if (preg_match('/^\/files\/js\/(\w*+)\.(\d*+)\.js$/', $url, $m)) {
            $name = $m[1] == 'moderator' ? 'comments' : $m[1];
            $prop = 'jsmerger_' . $name;
            $view = MainView::i();
            if (isset($view->$prop)) {
return $view->$prop;
}
        }

        if (preg_match('/^\/files\/js\/(\w*+)\.(\d*+)\.css$/', $url, $m)) {
            $name = 'cssmerger_' . $m[1];
            $view = MainView::i();
            if (isset($view->$name)) {
return $view->$name;
}
        }

        if ($url = $this->onget($url)) return $url;
        return false;
    }

} //class