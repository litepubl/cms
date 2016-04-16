<?php

namespace litepubl\admin;

class Link
{

    public static function url($path, $params = false) {
if ($params) {
        return litepubl::$site->url . $path . litepubl::$site->q . $params;
} else {
        return litepubl::$site->url . str_replace('?', litepubl::$site->q, $path);
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

        $a['href'] = str_replace('?', litepubl::$site->q, $a['href']);
        if (!strbegin($a['href'], 'http')) {
            $a['href'] = litepubl::$site->url . $a['href'];
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