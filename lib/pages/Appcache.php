<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\pages;
use litepubl\view\Theme;

class Appcache extends \litepubl\core\Items
{

    public function create() {
        parent::create();
        $this->basename = 'appcache.manifest';
        $this->dbversion = false;
        $this->data['url'] = '/manifest.appcache';
        $this->data['idurl'] = 0;
    }

    public function add($value) {
        if (!in_array($value, $this->items)) {
            $this->items[] = $value;
            $this->save();
            \ $this->getApp()->router->setexpired($this->idurl);
            $this->added($value);
        }
    }

    public function getText() {
        return implode("\r\n", $this->items);
    }

    public function setText($value) {
        $this->items = explode("\n", trim(str_replace(array(
            "\r\n",
            "\r"
        ) , "\n", $value)));
        $this->save();
    }

    public function request($arg) {
        $s = '<?php
    header(\'Content-Type: text/cache-manifest\');
    header(\'Last-Modified: ' . date('r') . '\');
    ?>';

        $s.= "CACHE MANIFEST\r\n";
        $s.= Theme::i()->parse($this->text);
        return $s;
    }

}