<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\admin;

class UList
{
    use \litepubl\core\AppTrait;

    const aslinks = true;
    public $ul;
    public $item;
    public $link;
    public $value;
    public $result;

    public function __construct($admin = null, $islink = false)
    {
        if ($admin) {
            $this->ul = $admin->templates['list'];
            $this->item = $admin->templates['list.item'];
            $this->link = $admin->templates['list.link'];
            $this->value = $admin->templates['list.value'];

            if ($islink == static ::aslinks) {
                $this->item = $this->link;
            }
        }

        $this->result = '';
    }

    public function li($name, $value)
    {
        return strtr(
            is_int($name) ? $this->value : $this->item, array(
            '$name' => $name,
            '$value' => $value,
            '$site.url' => $this->getApp()->site->url,
            )
        );
    }

    public function link($url, $title)
    {
        return strtr(
            $this->link, array(
            '$name' => $url,
            '$value' => $title,
            )
        );
    }

    public function ul($items)
    {
        return str_replace('$item', $items, $this->ul);
    }

    public function getResult()
    {
        return $this->ul($this->result);
    }

    public function add($name, $value)
    {
        $this->result.= $this->li($name, $value);
    }

    public function get(array $props)
    {
        $result = '';
        foreach ($props as $name => $value) {
            if ($value === false) {
                continue;
            }

            if (is_array($value)) {
                $value = $this->get($value);
            }

            $result.= $this->li($name, $value);
        }

        if ($result) {
            return $this->ul($result);
        }

        return '';
    }

    public function links(array $props)
    {
        $this->item = $this->link;
        $result = $this->get($props);
        return str_replace('$site.url', $this->getApp()->site->url, $result);
    }
}
