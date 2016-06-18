<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\admin;

use litepubl\core\Str;
use litepubl\view\Admin as AdminTheme;

class Link
{
    use \litepubl\core\AppTrait;

    public static function url($path, $params = false)
    {
        $site = static ::getAppInstance()->site;
        if ($params) {
            return $site->url . $path . $site->q . $params;
        } else {
            return $site->url . str_replace('?', $site->q, $path);
        }
    }

    public function parse($s)
    {
        $list = explode(',', $s);
        $a = array();
        foreach ($list as $item) {
            if ($i = strpos($item, '=')) {
                $a[trim(substr($item, 0, $i)) ] = trim(substr($item, $i + 1));
            } else {
                $a['text'] = trim($item);
            }
        }

        $site = static ::getAppInstance()->site;
        $a['href'] = str_replace('?', $site->q, $a['href']);
        if (!Str::begin($a['href'], 'http')) {
            $a['href'] = $site->url . $a['href'];
        }

        if (isset($a['icon'])) {
            $a['text'] = AdminTheme::admin()->getIcon($a['icon']) . (empty($a['text']) ? '' : ' ' . $a['text']);
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
