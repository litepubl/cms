<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

class Site extends Events
{
use PoolStorageTrait;

    public $mapoptions;
    private $users;

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

            return  $this->getApp()->options->data[$prop];
        }

        return parent::__get($name);
    }

    public function __set($name, $value) {
        if ($name == 'url') {
 return $this->seturl($value);
}


        if (in_array($name, $this->eventnames)) {
            $this->addevent($name, $value['class'], $value['func']);
        } elseif (isset($this->mapoptions[$name])) {
            $prop = $this->mapoptions[$name];
            if (is_string($prop))  $this->getApp()->options->{$prop} = $value;
        } elseif (!array_key_exists($name, $this->data) || ($this->data[$name] != $value)) {
            $this->data[$name] = $value;
            $this->save();
        }
        return true;
    }

    public function getUrl() {
        if ($this->fixedurl) {
 return $this->data['url'];
}


        return 'http://' .  $this->getApp()->domain;
    }

    public function getFiles() {
        if ($this->fixedurl) {
 return $this->data['files'];
}


        return 'http://' .  $this->getApp()->domain;
    }

    public function setUrl($url) {
        $url = rtrim($url, '/');
        $this->data['url'] = $url;
        $this->data['files'] = $url;
        $this->subdir = '';
        if ($i = strpos($url, '/', 10)) {
            $this->subdir = substr($url, $i);
        }
        $this->save();
    }

    public function getDomain() {
        return  $this->getApp()->domain;
    }

    public function getUserlink() {
        if ($id =  $this->getApp()->options->user) {
            if (!isset($this->users)) $this->users = array();
            if (isset($this->users[$id])) {
 return $this->users[$id];
}


            $item = tusers::i()->getitem($id);
            if ($item['website']) {
                $result = sprintf('<a href="%s">%s</a>', $item['website'], $item['name']);
            } else {
                $page = $this->getdb('userpage')->getitem($id);
                if ((int)$page['idurl']) {
                    $result = sprintf('<a href="%s%s">%s</a>', $this->url,  $this->getApp()->router->getvalue($page['idurl'], 'url') , $item['name']);
                } else {
                    $result = $item['name'];
                }
            }
            $this->users[$id] = $result;
            return $result;
        }
        return '';
    }

    public function getLiveuser() {
        return '<?php echo  $this->getApp()->site->getuserlink(); ?>';
    }

} 