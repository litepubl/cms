<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

//namespace litepubl\admin
class ulist {
  public $ul;
  public $item;
  public $link;
  public $value;

  public function __construct($admin = null) {
    if ($admin) {
      $this->ul = $admin->templates['list'];
      $this->item = $admin->templates['list.item'];
      $this->link = $admin->templates['list.link'];
      $this->value = $admin->templates['list.value'];
    }
  }

public function li($name, $value) {
return strtr(is_int($name) ? $this->value : $this->item, array(
        '$name' => $name,
        '$value' => $value,
      ));
}

public function link($url, $title) {
return strtr($this->link, array(
        '$name' => $url,
        '$value' => $title,
      ));
}

public function ul($items) {
      return str_replace('$item', $items, $this->ul);
}

  public function get(array $props) {
    $result = '';
    foreach ($props as $name => $value) {
      if ($value === false) continue;

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

  public function links(array $props) {
    $this->item = $this->link;
    $result = $this->get($props);
    return str_replace('$site.url', litepublisher::$site->url, $result);
  }

} //class