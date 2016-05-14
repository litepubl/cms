<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\plugins\externallinks;
use litepubl\core\Str;
use litepubl\core\Context;

class ExternalLinks extends \litepubl\core\Items implements \litepubl\core\ResponsiveInterface
 {
    public $exclude;

    protected function create() {
        $this->dbversion = true;
        parent::create();
        $this->table = 'externallinks';
        $this->basename = 'externallinks';
        $this->addmap('exclude', array());
    }

    public function add($url) {
        if ($id = $this->indexOf('url', $url)) {
 return $id;
}

        $item = array(
            'url' => $url,
            'clicked' => 0
        );

            $id = $this->db->add($item);
            $this->items[$id] = $item;
            return $id;
    }

    public function updateStat() {
        $filename =  $this->getApp()->paths->data . 'logs' . DIRECTORY_SEPARATOR . 'externallinks.txt';
        if (@file_exists($filename) && ($s = @file_get_contents($filename))) {
            @unlink($filename);
            $stat = array();
            $a = explode("\n", $s);
            foreach ($a as $id) {
                if ($id = (int)$id) {
                if (isset($stat[$id])) {
                    $stat[$id]++;
                } else {
                    $stat[$id] = 1;
                }
}
            }

            if (count($stat)) {
            $this->loadItems(array_keys($stat));
            foreach ($stat as $id => $clicked) {
                    $this->db->setValue($id, 'clicked', $clicked + $this->items[$id]['clicked']);
            }
}
    }
}

    public function request(Context $context) {
$response = $context->response;
$response->cache = true;

        $id = (int) $context->request->getArg('id', 0);
        if (!$this->itemExists($id)) {
 return $response->notfound();
}

        $item = $this->getItem($id);
        $url = $item['url'];
        $filename =  $this->getApp()->paths->data . 'logs' . DIRECTORY_SEPARATOR . 'externallinks.txt';

$response->redir($url);
$response->body = "<?php litepubl\\utils\\Filer::append('$filename', '$id\n'); ?>";
$response->cache = true;
$response->cacheHeader = false;
    }

    public function filter(&$content) {
        if (!preg_match_all('/<a\s*.*?href\s*=\s*[\'"]([^"\'>]*).*?>(.*?)<\/a>/i', $content, $links)) {
 return;
}

        $redir =  $this->getApp()->site->url . '/externallink.htm' .  $this->getApp()->site->q . 'id=';
$siteurl = $this->getApp()->site->url;
        $external = array();
        foreach ($links[1] as $num => $link) {
            if (isset($external[$link])) {
 continue;
}

            if (!Str::begin($link, 'http', 'ftp')) {
 continue;
}

            if (Str::begin($link,  $siteurl)) {
 continue;
}

            if ($this->inExclude($link)) {
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

    public function inExclude($link) {
        foreach ($this->exclude as $ex) {
            if (false !== strpos($link, $ex)) {
 return true;
}
        }

        return false;
    }

}