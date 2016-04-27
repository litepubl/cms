<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

class Router extends Items
 {
    public $prefilter;

    protected function create() {
        $this->dbversion = true;
        parent::create();
        $this->table = 'urlmap';
        $this->basename = 'urlmap';
        $this->addevents('beforerequest', 'afterrequest', 'onclearcache');
        $this->data['disabledcron'] = false;
        $this->data['redirdom'] = false;
        $this->addmap('prefilter', array());
    }

    public function request(Context $context)
{
$app = $this->getApp();
        if ($this->redirdom && $app->site->fixedurl) {
            $parsedUrl = parse_url( $app->site->url . '/');
            if ($context->request->host != strtolower($parsedUrl['host'])) {
$context->response->redir($app->site->url . $context->request->url);
return;
            }
        }

        $this->beforerequest($context);
        $context->itemRoute = $this->queryItem($context);
    }

    public function queryItem(Context $context)
 {
$url = $context->request->url;
        if ($result = $this->query($url)) {
            return $result;
        }

$srcurl = $url;
$response = $context->response;

        if ($i = strpos($url, '?')) {
            $url = substr($url, 0, $i);
        }

        if ('//' == substr($url, -2)) {
            $response->redir(rtrim($url, '/') . '/');
return false;
        }

        //extract page number
        if (preg_match('/(.*?)\/page\/(\d*?)\/?$/', $url, $m)) {
            if ('/' != substr($url, -1)) {
                $response->redir($url . '/');
return false;
            }

            $url = $m[1];
            if (!$url ) {
$url = '/';
}

            $context->request->page = max(1, abs((int)$m[2]));
        }

        if (($srcurl != $url) && ($result = $this->query($url))) {
            if (($context->request->page == 1) && ($result['type'] == 'normal') && ($srcurl != $result['url'])) {
                $response->redir($result['url']);
            }

            return $result;
        }

        $url = $url != rtrim($url, '/') ? rtrim($url, '/') : $url . '/';
        if (($srcurl != $url) && ($result = $this->query($url))) {
            if (($this->page == 1) && ($result['type'] == 'normal') && ($srcurl != $result['url'])) {
                $response->redir($result['url']);
            }

            return $result;
        }

        $context->request->uripath = explode('/', trim($url, '/'));
        return false;
    }

    public function getIdurl($id) {
        if (!isset($this->items[$id])) {
            $this->items[$id] = $this->db->getitem($id);
        }
        return $this->items[$id]['url'];
    }

    public function findurl($url) {
        if ($result = $this->db->findItem('url = ' . Str::quote($url))) {
            return $result;
        }

        return false;
    }

    public function urlexists($url) {
        return $this->db->findid('url = ' . Str::quote($url));
    }

    private function query($url) {
        if ($item = $this->findfilter($url)) {
            $this->items[$item['id']] = $item;
            return $item;
        } else if ($item = $this->db->getassoc('url = ' . Str::quote($url) . ' limit 1')) {
            $this->items[$item['id']] = $item;
            return $item;
        }

        return false;
    }


    public function findfilter($url) {
        foreach ($this->prefilter as $item) {
            switch ($item['type']) {
                case 'begin':
                    if (Str::begin($url, $item['url'])) {
                        return $item;
                    }
                    break;


                case 'end':
                    if (Str::end($url, $item['url'])) {
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

        if ($item = $this->db->findItem('url = ' . Str::quote($url))) {
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
        $url = Str::quote($url);
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
        if ($items = $this->db->getitems('class = ' . Str::quote($class))) {
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
        $res = $this->db->query("select url from $this->thistable where class = " . Str::quote($class));
        return $this->db->res2id($res);
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
    public function setUrlvalue($url, $name, $value) {
        if ($id = $this->urlexists($url)) {
            $this->setvalue($id, $name, $value);
        }
    }

    public function setIdurl($id, $url) {
        $this->db->setvalue($id, 'url', $url);
        if (isset($this->items[$id])) $this->items[$id]['url'] = $url;
    }

//backward compabilty
public function clearcache()
{
$this->getApp()->cache->clear();
}

}