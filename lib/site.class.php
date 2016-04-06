<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tsite extends tevents_storage {
    public $mapoptions;
    private $users;

    public static function i() {
        return getinstance(__class__);
    }

    public static function instance() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'site';
        $this->addmap('mapoptions', array(
            'version' => 'version',
            'language' => 'language',
        ));
    }

    public function __get($name) {
        if (isset($this->mapoptions[$name])) {
            $prop = $this->mapoptions[$name];
            if (is_array($prop)) {
                list($classname, $method) = $prop;
                return call_user_func_array(array(
                    getinstance($classname) ,
                    $method
                ) , array(
                    $name
                ));
            }

            return litepubl::$options->data[$prop];
        }

        return parent::__get($name);
    }

    public function __set($name, $value) {
        if ($name == 'url') return $this->seturl($value);
        if (in_array($name, $this->eventnames)) {
            $this->addevent($name, $value['class'], $value['func']);
        } elseif (isset($this->mapoptions[$name])) {
            $prop = $this->mapoptions[$name];
            if (is_string($prop)) litepubl::$options->{$prop} = $value;
        } elseif (!array_key_exists($name, $this->data) || ($this->data[$name] != $value)) {
            $this->data[$name] = $value;
            $this->save();
        }
        return true;
    }

    public function geturl() {
        if ($this->fixedurl) return $this->data['url'];
        return 'http://' . litepubl::$domain;
    }

    public function getfiles() {
        if ($this->fixedurl) return $this->data['files'];
        return 'http://' . litepubl::$domain;
    }

    public function seturl($url) {
        $url = rtrim($url, '/');
        $this->data['url'] = $url;
        $this->data['files'] = $url;
        $this->subdir = '';
        if ($i = strpos($url, '/', 10)) {
            $this->subdir = substr($url, $i);
        }
        $this->save();
    }

    public function getdomain() {
        return litepubl::$domain;
    }

    public function getuserlink() {
        if ($id = litepubl::$options->user) {
            if (!isset($this->users)) $this->users = array();
            if (isset($this->users[$id])) return $this->users[$id];
            $item = tusers::i()->getitem($id);
            if ($item['website']) {
                $result = sprintf('<a href="%s">%s</a>', $item['website'], $item['name']);
            } else {
                $page = $this->getdb('userpage')->getitem($id);
                if ((int)$page['idurl']) {
                    $result = sprintf('<a href="%s%s">%s</a>', $this->url, litepubl::$urlmap->getvalue($page['idurl'], 'url') , $item['name']);
                } else {
                    $result = $item['name'];
                }
            }
            $this->users[$id] = $result;
            return $result;
        }
        return '';
    }

    public function getliveuser() {
        return '<?php echo litepubl::$site->getuserlink(); ?>';
    }

} //class