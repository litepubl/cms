<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class ticons extends titems {

    public static function i() {
        return getinstance(__class__);
    }

    public function getId($name) {
        return isset($this->items[$name]) ? $this->items[$name] : 0;
    }

    public function getUrl($name) {
        if (isset($this->items[$name])) {
            $files = tfiles::i();
            return $files->geturl($this->items[$name]);
        }
        return '';
    }

    public function getIcon($name) {
        if (isset($this->items[$name]) && ($this->items[$name] > 0)) {
            $files = tfiles::i();
            return $files->geticon($this->items[$name]);
        }
        return '';
    }

    public function filedeleted($idfile) {
        foreach ($this->items as $name => $id) {
            if ($id == $idfile) {
                $this->delete($name);
                return true;
            }
        }
    }

} //class