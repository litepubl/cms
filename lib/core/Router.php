<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

class Router extends Items
 {
    public $adminpanel;
    public $cache_enabled;
    public $host;
    public $is404;
    public $isredir;
    public $item;
    public $model;
    public $page;
    public $url;
    public $uripath;
    public $prefilter;
    protected $close_events;

    protected function create() {
        $this->dbversion = true;
        parent::create();
        $this->table = 'urlmap';
        $this->basename = 'urlmap';
        $this->addevents('beforerequest', 'afterrequest', 'onclearcache');
        $this->data['disabledcron'] = false;
        $this->data['redirdom'] = false;
        $this->addmap('prefilter', array());

        $this->is404 = false;
        $this->isredir = false;
        $this->adminpanel = false;
        $this->cache_enabled = litepubl::$options->cache && !litepubl::$options->admincookie;
        $this->page = 1;
        $this->close_events = array();
    }

    protected function prepareurl($host, $url) {
        $this->host = $host;
        $this->page = 1;
        $this->uripath = array();
        if (litepubl::$site->q == '?') {
            $this->url = substr($url, strlen(litepubl::$site->subdir));
        } else {
            $this->url = $_GET['url'];
        }
    }

    public function request($host, $url) {
        $this->prepareurl($host, $url);
        $this->adminpanel = strbegin($this->url, '/admin/') || ($this->url == '/admin');
        if ($this->redirdom) {
            $parsedurl = parse_url(litepubl::$site->url . '/');
            if ($host != strtolower($parsedurl['host'])) {
                return $this->redir($url);
            }
        }

        $this->beforerequest();
        if (!litepubl::$debug && litepubl::$options->ob_cache) {
            ob_start();
        }

        try {
            $this->dorequest($this->url);
        }
        catch(\Exception $e) {
            litepubl::$options->handexception($e);
        }

        // production mode: no debug and enabled buffer
        if (!litepubl::$debug && litepubl::$options->ob_cache) {
            litepubl::$options->showerrors();
            litepubl::$options->errorlog = '';

            $afterclose = $this->isredir || count($this->close_events);
            if ($afterclose) {
                $this->close_connection();
            }

            while (@ob_end_flush());
            flush();

            if ($afterclose) {
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }

                ob_start();
            }
        }

        $this->afterrequest($this->url);
        $this->close();
    }

    public function close_connection() {
        ignore_user_abort(true);
        $len = ob_get_length();
        header('Connection: close');
        header('Content-Length: ' . $len);
        header('Content-Encoding: none');
    }

    protected function dorequest($url) {
        $this->item = $this->find_item($url);

        if ($this->isredir) {
            return;
        }

        if ($this->item) {
            return $this->printcontent($this->item);
        } else {
            $this->notfound404();
        }
    }

    public function getidurl($id) {
        if (!isset($this->items[$id])) {
            $this->items[$id] = $this->db->getitem($id);
        }
        return $this->items[$id]['url'];
    }

public function getView() {
return \litepubl\view\MainView::i();
}

    public function findurl($url) {
        if ($result = $this->db->finditem('url = ' . dbquote($url))) {
            return $result;
        }

        return false;
    }

    public function urlexists($url) {
        return $this->db->findid('url = ' . dbquote($url));
    }

    private function query($url) {
        if ($item = $this->findfilter($url)) {
            $this->items[$item['id']] = $item;
            return $item;
        } else if ($item = $this->db->getassoc('url = ' . dbquote($url) . ' limit 1')) {
            $this->items[$item['id']] = $item;
            return $item;
        }

        return false;
    }

    public function find_item($url) {
        if ($result = $this->query($url)) {
            return $result;
        }

        $srcurl = $url;
        if ($i = strpos($url, '?')) {
            $url = substr($url, 0, $i);
        }

        if ('//' == substr($url, -2)) {
            $this->redir(rtrim($url, '/') . '/');
        }

        //extract page number
        if (preg_match('/(.*?)\/page\/(\d*?)\/?$/', $url, $m)) {
            if ('/' != substr($url, -1)) {
                return $this->redir($url . '/');
            }

            $url = $m[1];
            if ($url == '') $url = '/';
            $this->page = max(1, abs((int)$m[2]));
        }

        if (($srcurl != $url) && ($result = $this->query($url))) {
            if (($this->page == 1) && ($result['type'] == 'normal') && ($srcurl != $result['url'])) {
                return $this->redir($result['url']);
            }

            return $result;
        }

        $url = $url != rtrim($url, '/') ? rtrim($url, '/') : $url . '/';
        if (($srcurl != $url) && ($result = $this->query($url))) {
            if (($this->page == 1) && ($result['type'] == 'normal') && ($srcurl != $result['url'])) {
                return $this->redir($result['url']);
            }

            return $result;
        }

        $this->uripath = explode('/', trim($url, '/'));
        return false;
    }

    public function findfilter($url) {
        foreach ($this->prefilter as $item) {
            switch ($item['type']) {
                case 'begin':
                    if (strbegin($url, $item['url'])) {
                        return $item;
                    }
                    break;


                case 'end':
                    if (strend($url, $item['url'])) {
                        return $item;
                    }
                    break;


                case 'regexp':
                    if (preg_match($item['url'], $url)) {
                        return $item;
                    }
                    break;
            }
        }

        return false;
    }

    public function updatefilter() {
        $this->prefilter = $this->db->getitems('type in (\'begin\', \'end\', \'regexp\')');
        $this->save();
    }

    private function getcachefile(array $item) {
        switch ($item['type']) {
            case 'normal':
                return sprintf('%s-%d.php', $item['id'], $this->page);

            case 'usernormal':
                return sprintf('%s-page-%d-user-%d.php', $item['id'], $this->page, litepubl::$options->user);

            case 'userget':
                return sprintf('%s-page-%d-user%d-get-%s.php', $item['id'], $this->page, litepubl::$options->user, md5($_SERVER['REQUEST_URI']));

            default: //get
                return sprintf('%s-%d-%s.php', $item['id'], $this->page, md5($_SERVER['REQUEST_URI']));
        }
    }

    protected function save_file($filename, $content) {
        $this->cache->setString($filename, $content);
    }

    protected function include_file($fn) {
        if (litepubl::$memcache) {
            if ($s = $this->cache->getString($fn)) {
                eval('?>' . $s);
                return true;
            }
            return false;
        }

        $filename = litepubl::$paths->cache . $fn;
        if (file_exists($filename) && ((filemtime($filename) + litepubl::$options->expiredcache - litepubl::$options->filetime_offset) >= time())) {
            include ($filename);
            return true;
        }

        return false;
    }

    private function printcontent(array $item) {
        $options = litepubl::$options;
        if ($this->cache_enabled && $this->include_file($this->getcachefile($item))) {
            return;
        }

        if (class_exists($item['class'])) {
            return $this->GenerateHTML($item);
        } else {
            $this->notfound404();
        }
    }

    public function getidmodel($id) {
        $item = $this->getitem($id);
        return $this->getmodel($item);
    }

    public function getmodel(array $item) {
        $classname = $item['class'];
        $parents = class_parents($classname);
        if (in_array('litepubl\titem', $parents)) {
            return call_user_func_array(array(
                $classname,
                'i'
            ) , array(
                $item['arg']
            ));
        } else {
            return litepubl::$classes->getinstance($classname);
        }
    }

    protected function GenerateHTML(array $item) {
        $model = $this->getmodel($item);
        $this->model = $model;

        //special handling for rss
        if (method_exists($model, 'request') && ($s = $model->request($item['arg']))) {
            switch ($s) {
                case 404:
                    return $this->notfound404();
                case 403:
                    return $this->forbidden();
            }
        } else {
            if ($this->isredir) {
                return;
            }

            $view = $this->getView();
            $s = $view->render($model);
        }

        eval('?>' . $s);
        if ($this->cache_enabled && $model->cache) {
            $this->save_file($this->getcachefile($item) , $s);
        }
    }

    public function notfound404() {
        $redir = Redirector::i();
        if ($url = $redir->get($this->url)) {
            return $this->redir($url);
        }

        $this->is404 = true;
        $this->printclasspage('litepubl\pages\Notfound404');
    }

    private function printclasspage($classname) {
        $cachefile = str_replace('\\', '_', $classname) . '.php';
        if ($this->cache_enabled && $this->include_file($cachefile)) {
            return;
        }

        $obj = litepubl::$classes->getinstance($classname);
        $view = $this->getView();
        $s = $view->render($obj);
        eval('?>' . $s);

        if ($this->cache_enabled && $obj->cache) {
            $this->cache->set($cachefile, $result);
        }
    }

    public function forbidden() {
        $this->is404 = true;
        $this->printclasspage('litepubl\pages\Forbidden');
    }

    public function addget($url, $class) {
        return $this->add($url, $class, null, 'get');
    }

    public function add($url, $class, $arg, $type = 'normal') {
        if (empty($url)) $this->error('Empty url to add');
        if (empty($class)) $this->error('Empty class name of adding url');
        if (!in_array($type, array(
            'normal',
            'get',
            'usernormal',
            'userget',
            'begin',
            'end',
            'regexp'
        ))) {
            $this->error(sprintf('Invalid url type %s', $type));
        }

        if ($item = $this->db->finditem('url = ' . dbquote($url))) {
            $this->error(sprintf('Url "%s" already exists', $url));
        }

        $item = array(
            'url' => $url,
            'class' => $class,
            'arg' => (string)$arg,
            'type' => $type
        );

        $item['id'] = $this->db->add($item);
        $this->items[$item['id']] = $item;

        if (in_array($type, array(
            'begin',
            'end',
            'regexp'
        ))) {
            $this->prefilter[] = $item;
            $this->save();
        }

        return $item['id'];
    }

    public function delete($url) {
        $url = dbquote($url);
        if ($id = $this->db->findid('url = ' . $url)) {
            $this->db->iddelete($id);
        } else {
            return false;
        }

        foreach ($this->prefilter as $i => $item) {
            if ($id == $item['id']) {
                unset($this->prefilter[$i]);
                $this->save();
                break;
            }
        }

        $this->clearcache();
        $this->deleted($id);
        return true;
    }

    public function deleteclass($class) {
        if ($items = $this->db->getitems('class = ' . dbquote($class))) {
            foreach ($items as $item) {
                $this->db->iddelete($item['id']);
                $this->deleted($item['id']);
            }
        }

        $this->clearcache();
    }

    public function deleteitem($id) {
        if ($item = $this->db->getitem($id)) {
            $this->db->iddelete($id);
            $this->deleted($id);
        }
        $this->clearcache();
    }

    //for Archives
    public function GetClassUrls($class) {
        $res = $this->db->query("select url from $this->thistable where class = " . dbquote($class));
        return $this->db->res2id($res);
    }

    public function clearcache() {
        $this->cache->clear();
        $this->onclearcache();
    }

    public function setexpired($id) {
        if ($item = $this->getitem($id)) {
            $cache = $this->cache;
            $page = $this->page;
            for ($i = 1; $i <= 10; $i++) {
                $this->page = $i;
                $cache->delete($this->getcachefile($item));
            }
            $this->page = $page;
        }
    }

    public function setexpiredcurrent() {
        $this->cache->delete($this->getcachefile($this->item));
    }

    public function expiredclass($class) {
        $items = $this->db->getitems('class = ' . dbquote($class));
        if (!count($items)) {
            return;
        }

        $cache = $this->cache;
        $page = $this->page;
        $this->page = 1;
        foreach ($items as $item) {
            $cache->delete($this->getcachefile($item));
        }
        $this->page = $page;
    }

    public function addredir($from, $to) {
        if ($from == $to) {
            return;
        }

        $Redir = tredirector::i();
        $Redir->add($from, $to);
    }

    public static function unsub($obj) {
static ::i()->unbind($obj);
}

public function unbind($obj) {
        $this->lock();
parent::unbind($obj);
        $this->deleteclass(get_class($obj));
        $this->unlock();
    }

    public function setonclose(array $a) {
        if (count($a) == 0) {
            return;
        }

        $this->close_events[] = $a;
    }

    public function onclose() {
        $this->setonclose(func_get_args());
    }

    private function call_close_events() {
        foreach ($this->close_events as $a) {
            try {
                $c = array_shift($a);

                if (!is_callable($c)) {
                    $c = array(
                        $c,
                        array_shift($a)
                    );
                }

                call_user_func_array($c, $a);
            }
            catch(Exception $e) {
                litepubl::$options->handexception($e);
            }
        }

        $this->close_events = array();
    }

    protected function close() {
        $this->call_close_events();
        if ($this->disabledcron || ($this->model && (get_class($this->model) == 'litepubl\tcron'))) {
            return;
        }

        $memvars = Memvars::i();
        if ($memvars->hourcron + 3600 <= time()) {
            $memvars->hourcron = time();
            $memvars->singlecron = false;
            Cron::pingonshutdown();
        } else if ($memvars->singlecron && ($memvars->singlecron <= time())) {
            $memvars->singlecron = false;
            Cron::pingonshutdown();
        }
    }

    public function redir($url, $status = 301) {
        litepubl::$options->savemodified();
        $this->isredir = true;

        switch ($status) {
            case 301:
                header('HTTP/1.1 301 Moved Permanently', true, 301);
                break;


            case 302:
                header('HTTP/1.1 302 Found', true, 302);
                break;


            case 307:
                header('HTTP/1.1 307 Temporary Redirect', true, 307);
                break;
        }

        if (!strbegin($url, 'http://') && !strbegin($url, 'https://')) $url = litepubl::$site->url . $url;
        header('Location: ' . $url);
    }

    public function seturlvalue($url, $name, $value) {
        if ($id = $this->urlexists($url)) {
            $this->setvalue($id, $name, $value);
        }
    }

    public function setidurl($id, $url) {
        $this->db->setvalue($id, 'url', $url);
        if (isset($this->items[$id])) $this->items[$id]['url'] = $url;
    }

    public function getnextpage() {
        $url = $this->item['url'];
        return litepubl::$site->url . rtrim($url, '/') . '/page/' . ($this->page + 1) . '/';
    }

    public function getprevpage() {
        $url = $this->item['url'];
        if ($this->page <= 2) {
            return url;
        }

        return litepubl::$site->url . rtrim($url, '/') . '/page/' . ($this->page - 1) . '/';
    }

    public static function htmlheader($cache) {
        return sprintf('<?php litepubl\turlmap::sendheader(%s); ?>', $cache ? 'true' : 'false');
    }

    public static function nocache() {
        Header('Cache-Control: no-cache, must-revalidate');
        Header('Pragma: no-cache');
    }

    public static function sendheader($cache) {
        if (!$cache) {
            static ::nocache();
        }

        header('Content-Type: text/html; charset=utf-8');
        header('Last-Modified: ' . date('r'));
        header('X-Pingback: ' . litepubl::$site->url . '/rpc.xml');
    }

    public static function sendxml() {
        header('Content-Type: text/xml; charset=utf-8');
        header('Last-Modified: ' . date('r'));
        header('X-Pingback: ' . litepubl::$site->url . '/rpc.xml');
        echo '<?xml version="1.0" encoding="utf-8" ?>';
    }

} //class