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

class texternallinks extends titems {
    public $exclude;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        $this->dbversion = dbversion;
        parent::create();
        $this->table = 'externallinks';
        $this->basename = 'externallinks';
        $this->addmap('exclude', array());
    }

    public function add($url) {
        if ($id = $this->indexof('url', $url)) {
 return $id;
}


        $item = array(
            'url' => $url,
            'clicked' => 0
        );

        if ($this->dbversion) {
            $id = $this->db->add($item);
            $this->items[$id] = $item;
            return $id;
        } else {
            $this->items[++$this->autoid] = $item;
            $this->save();
            return $this->autoid;
        }
    }

    public function updatestat() {
        $filename =  $this->getApp()->paths->data . 'logs' . DIRECTORY_SEPARATOR . 'externallinks.txt';
        if (@file_exists($filename) && ($s = @file_get_contents($filename))) {
            @unlink($filename);
            $stat = array();
            $a = explode("\n", $s);
            foreach ($a as $id) {
                $id = (int)$id;
                if ($id == 0) {
 continue;
}


                if (isset($stat[$id])) {
                    $stat[$id]++;
                } else {
                    $stat[$id] = 1;
                }
            }

            if (count($stat) == 0) {
 return;
}


            $this->loaditems(array_keys($stat));
            foreach ($stat as $id => $clicked) {
                if ($this->dbversion) {
                    $this->db->setvalue($id, 'clicked', $clicked + $this->items[$id]['clicked']);
                } else {
                    $this->items[$id]['clicked']+= $clicked;
                }
            }
            $this->save();
        }
    }

    public function request($arg) {
        //$this->cache = false;
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$this->itemexists($id)) {
 return 404;
}


        $item = $this->getitem($id);
        $url = $item['url'];
        $filename =  $this->getApp()->paths->data . 'logs' . DIRECTORY_SEPARATOR . 'externallinks.txt';
        return "<?php tfiler::append('$id\n', '$filename');
    litepubl::\$router->redir('$url');";
    }

    public function filter(&$content) {
        if (!preg_match_all('/<a\s*.*?href\s*=\s*[\'"]([^"\'>]*).*?>(.*?)<\/a>/i', $content, $links)) {
 return;
}


        $redir =  $this->getApp()->site->url . '/externallink.htm' .  $this->getApp()->site->q . 'id=';
        $external = array();
        foreach ($links[1] as $num => $link) {
            if (isset($external[$link])) {
 continue;
}


            if (!Str::begin($link, 'http', 'ftp')) {
 continue;
}


            if (Str::begin($link,  $this->getApp()->site->url)) {
 continue;
}


            if ($this->inexclude($link)) {
 continue;
}


            $id = $this->add($link);
            $external[$link] = $redir . $id;
        }

        foreach ($external as $src => $dst) {
            $content = str_replace(sprintf('"%s"', $src) , sprintf('"%s"', $dst) , $content);
            $content = str_replace(sprintf("'%s'", $src) , sprintf("'%s'", $dst) , $content);
        }
    }

    public function inexclude($link) {
        foreach ($this->exclude as $ex) {
            if (false !== strpos($link, $ex)) {
 return true;
}


        }
        return false;
    }

} //class