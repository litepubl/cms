<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\core\Str;

class tfoaf extends titems {
    public $title;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        $this->dbversion = dbversion;
        parent::create();
        $this->basename = 'foaf';
        $this->table = 'foaf';
    }

    public function getApproved($count) {
        $count = (int)$count;
        if ($this->dbversion) {
            $limit = $count == 0 ? '' : " limit 0, $count";
            if ($result = $this->select("status = 'approved'", "order by added desc" . $limit)) {
 return $result;
}


            return array();
        } else {
            $result = array_keys($this->items);
            if ($count > 0) {
                $result = array_slice($items, 0, $count);
            }
            return $result;
        }
    }

    public function request($arg) {
        return '<?php \litepubl\core\Router::sendxml(); ?>' . $this->getfoafxml();
    }

    public function add($nick, $url, $foafurl, $status) {
        $item = array(
            'nick' => $nick,
            'url' => $url,
            'foafurl' => $foafurl,
            'added' => Str::sqlDate() ,
            'errors' => 0,
            'status' => $status
        );

        if ($this->dbversion) {
            $id = $this->db->add($item);
        } else {
            $id = ++$this->autoid;
        }
        $this->items[$id] = $item;
        if (!$this->dbversion) $this->save();
        $this->added($id);
        $router = \litepubl\core\Router::i();
        $router->clearcache();
        return $id;
    }

    public function edit($id, $nick, $url, $foafurl, $status) {
        $item = $this->getitem($id);
        $item['nick'] = $nick;
        $item['url'] = $url;
        $item['foafurl'] = $foafurl;
        $item['status'] = $status;
        $this->changestatus($id, $status);

        if ($this->dbversion) {
            $this->db->updateassoc($item);
        }
        $this->items[$id] = $item;
        if (!$this->dbversion) $this->save();
        $this->edited($id);
        $router = \litepubl\core\Router::i();
        $router->clearcache();
        return true;
    }

    public function delete($id) {
        if (parent::delete($id)) {
            $router = \litepubl\core\Router::i();
            $router->clearcache();
            return true;
        }
        return false;
    }

    public function deleteurl($url) {
        if ($this->dbversion) {
            $this->db->delete('url = ' . Str::uuote($url));
        } else {
            foreach ($this->items as $id => $item) {
                if ($url == $item['url']) {
 return $this->Delete($id);
}


            }
        }
    }

    private function getFoafxml() {
        $result = '<rdf:RDF ' . 'xml:lang="en" ' . 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" ' . 'xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" ' . 'xmlns:foaf="http://xmlns.com/foaf/0.1/" ' . 'xmlns:ya="http://blogs.yandex.ru/schema/foaf/" ' . 'xmlns:lj="http://www.livejournal.org/rss/lj/1.0/" ' . 'xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" ' . 'xmlns:dc="http://purl.org/dc/elements/1.1/">' . '<foaf:Person>';

        $profile = tprofile::i();
        $result.= $profile->getfoaf();
        $result.= $this->getknows();

        $result.= '</foaf:Person></rdf:RDF>';
        return $result;
    }

    private function getKnows() {
        $result = '';
        foreach ($this->items as $id => $item) {
            $result.= '<foaf:knows>' . '<foaf:Person>' . '<foaf:nick>' . static ::escape($item['nick']) . '</foaf:nick>' . '<rdfs:seeAlso rdf:resource="' . static ::escape($item['foafurl']) . '"/>' . '<foaf:weblog rdf:resource="' . static ::escape($item['url']) . '"/>' . '</foaf:Person>' . '</foaf:knows>';
        }

        return $result;
    }

    public function hasfriend($url) {
        if ($this->dbversion) {
            return $this->select('url = ' . Str::uuote($url) , 'limit 1');
        } else {
            foreach ($this->items as $id => $item) {
                if ($url == $item['url']) {
 return $id;
}


            }
            return false;
        }
    }

    public function setStatus($id, $value) {
        if ($this->itemexists($id)) $this->setvalue($id, 'status', $value);
        if (!$this->dbversion) $this->save();
    }

    public function changestatus($id, $value) {
        if (!$this->itemexists($id)) {
 return false;
}


        $item = $this->getitem($id);
        if ($item['status'] == $value) {
 return false;
}


        $this->setvalue($id, 'status', $value);
        if ($item['status'] == 'hold') {
            if ($value == 'approved') {
                $this->acceptinvate($id);
            } elseif ($value == 'rejected') {
                $this->rejectinvate($id);
            }
        }
    }

    private function getDomain($url) {
        $url = strtolower(trim($url));
        if (preg_match('/(http:\/\/|https:\/\/|)(www\.|)([-\.\w]+)\/?/', $url, $found)) {
            return isset($found[3]) && !empty($found[3]) ? $found[3] : false;
        }
        return false;
    }

    private function validateurl($url, $foafurl) {
        if (($url = $this->getdomain($url)) && ($foafurl = $this->getdomain($foafurl))) {
            $self = $this->getdomain( $this->getApp()->site->url);
            return ($url == $foafurl) && ($url != $self);
        }
        return false;
    }
    /* begin remote calls */
    public function invate($nick, $url, $foafurl) {
        if (!$this->validateurl($url, $foafurl)) {
 return false;
}


        if ($this->hasfriend($url)) {
 return false;
}


        $id = $this->add($nick, $url, $foafurl, 'hold');
        $this->sendmail($id, 'invated');
        return true;
    }

    public function accept($nick, $url, $foafurl) {
        if (!$this->validateurl($url, $foafurl)) {
 return false;
}


        $id = $this->hasfriend($url);
        if (!$id) {
 return false;
}


        $item = $this->getitem($id);
        if ($item['status'] == 'approved') {
 return true;
}


        if (!(($item['status'] == 'invated') || ($item['status'] == 'hold'))) {
 return false;
}


        $this->setstatus($id, 'approved');
        $this->sendmail($id, 'accepted');
        return true;
    }

    public function reject($nick, $url, $foafurl) {
        if (!$this->validateurl($url, $foafurl)) {
 return false;
}


        if ($id = $this->hasfriend($url)) {
            $this->sendmail($id, 'rejected');
            //$this->delete($id);
            $this->setstatus($id, 'rejected');
            return true;
        }
        return false;
    }
    /* end remote calls */
    private function sendmail($id, $event) {
        $item = $this->getitem($id);
        $args = new Args();
        $args->add($item);
        $lang = Lang::i('foaf');
        $event = 'mail' . $event;
        $args->event = $lang->$event;

        Lang::usefile('mail');
        $lang = Lang::i('mailfoaf');
        $theme = ttheme::i();

        $subject = $theme->parsearg($lang->subject, $args);
        $body = $theme->parsearg($lang->body, $args);

        tmailer::sendtoadmin($subject, $body);
    }

    protected function getProfile() {
        $profile = tprofile::i();
        return array(
            'nick' => $profile->nick,
            'url' =>  $this->getApp()->site->url .  $this->getApp()->site->home,
            'foafurl' =>  $this->getApp()->site->url . '/foaf.xml'
        );
    }

    public function addurl($url) {
        if ($ping = tpinger::discover($url)) {
            $actions = TXMLRPCAction::i();
            if ($actions->invatefriend($ping, $this->profile)) {
                $util = tfoafutil::i();
                if ($info = $util->getinfo($url)) {
                    return $this->add($info['nick'], $info['url'], $info['foafurl'], 'invated');
                }
            }
        }
        return false;
    }

    public function acceptinvate($id) {
        if (!$this->itemexists($id)) {
 return false;
}


        $item = $this->getitem($id);
        if ($ping = tpinger::Discover($item['url'])) {
            $actions = TXMLRPCAction::i();
            if ($actions->acceptfriend($ping, $this->profile)) {
                $this->setstatus($id, 'approved');
                return true;
            }
        }
        return false;
    }

    public function rejectinvate($id) {
        if (!$this->itemexists($id)) {
 return false;
}


        $item = $this->getitem($id);
        $this->setstatus($id, 'rejected');
        if ($ping = tpinger::Discover($item['url'])) {
            $actions = TXMLRPCAction::i();
            if ($actions->rejectfriend($ping, $this->profile)) {
                return true;
            }
        }
        return false;
    }

    public static function escape($s) {
        return strtr(htmlspecialchars($s) , array(
            '"' => '&quot;',
            "'" => '&#039;',
            '\\' => '&#092;'
        ));
    }

    public static function getParam($name, $value) {
        return sprintf('<foaf:%1$s>%2$s</foaf:%1$s>', $name, static ::escape($value));
    }

} //class