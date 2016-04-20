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

class tsmushitplugin extends tplugin {

    public static function i() {
        return getinstance(__class__);
    }

    public function install() {

        $parser = tmediaparser::i();
        $parser->added = $this->fileadded;
    }

    public function uninstall() {
        $parser = tmediaparser::i();
        $parser->unbind($this);
    }

    public function fileadded($id) {
        $files = tfiles::i();
        $item = $files->getitem($id);
        if ('image' != $item['media']) {
 return;
}


        $fileurl = $files->geturl($id);
        if ($s = http::get('http://www.smushit.com/ysmush.it/ws.php?img=' . urlencode($fileurl))) {
            $json = json_decode($s);
            if (isset($json->error) || (-1 === (int)$json->dest_size) || !$json->dest) {
 return;
}


            $div = $item['size'] - (int)$json->dest_size;
            if (($div / ($item['size'] / 100)) < 3) {
 return;
}


            $dest = urldecode($json->dest);
            if (!Str::begin($dest, 'http')) $dest = 'http://www.smushit.com/' . $dest;
            if ($content = http::get($dest)) {
                return $files->setcontent($id, $content);
            }
        }
    }

} //class