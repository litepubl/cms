<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class torderwidget extends twidget {

  protected function create() {
    parent::create();
    unset($this->id);
    $this->data['id'] = 0;
    $this->data['ajax'] = false;
    $this->data['order'] = 0;
    $this->data['sidebar'] = 0;
  }

  public function onsidebar(array & $items, $sidebar) {
    if ($sidebar != $this->sidebar) return;
    $order = $this->order;
    if (($order < 0) || ($order >= count($items))) $order = count($items);
    array_insert($items, array(
      'id' => $this->id,
      'ajax' => $this->ajax
    ) , $order);
  }

} //class