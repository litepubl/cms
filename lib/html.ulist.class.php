<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class ulist {
const aslinks = true;
  public $ul;
  public $item;
  public $link;
  public $value;
public $result;

  public function __construct($admin = null, $islink = false) {
    if ($admin) {
      $this->ul = $admin->templates['list'];
      $this->item = $admin->templates['list.item'];
      $this->link = $admin->templates['list.link'];
      $this->value = $admin->templates['list.value'];

if ($islink == self::aslinks) {
$this->item = $this->link;
}
    }

$this->result = '';
  }

  public function li($name, $value) {
    return strtr(is_int($name) ? $this->value : $this->item, array(
      '$name' => $name,
      '$value' => $value,
'$site.url' => litepublisher::$site->url,
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

public function getresult() {
return $this->ul($this->result);
}

public function add($name, $value) {
$this->result .= $this->li($name, $value);
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