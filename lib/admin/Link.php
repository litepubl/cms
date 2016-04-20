<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin;
use litepubl\core\Str;

class Link
{

    public static function url($path, $params = false) {
if ($params) {
        return  $this->getApp()->site->url . $path .  $this->getApp()->site->q . $params;
} else {
        return  $this->getApp()->site->url . str_replace('?',  $this->getApp()->site->q, $path);
    }
}

    public function parse($s) {
        $list = explode(',', $s);
        $a = array();
        foreach ($list as $item) {
            if ($i = strpos($item, '=')) {
                $a[trim(substr($item, 0, $i)) ] = trim(substr($item, $i + 1));
            } else {
                $a['text'] = trim($item);
            }
        }

        $a['href'] = str_replace('?',  $this->getApp()->site->q, $a['href']);
        if (!Str::begin($a['href'], 'http')) {
            $a['href'] =  $this->getApp()->site->url . $a['href'];
        }

        if (isset($a['icon'])) {
            $a['text'] = $this->geticon($a['icon']) . (empty($a['text']) ? '' : ' ' . $a['text']);
        }

        if (isset($a['tooltip'])) {
            $a['title'] = $a['tooltip'];
            $a['class'] = empty($a['class']) ? 'tooltip-toggle' : $a['class'] . ' tooltip-toggle';
        }

        $attr = '';
        foreach (array(
            'class',
            'title',
            'role'
        ) as $name) {
            if (!empty($a[$name])) {
                $attr.= sprintf(' %s="%s"', $name, $a[$name]);
            }
        }

        return sprintf('<a href="%s"%s>%s</a>', $a['href'], $attr, $a['text']);
    }

}