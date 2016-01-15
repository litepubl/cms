<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tpoolpolls extends tpullitems {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->basename = 'pullpolls';
  }

  public function getitem($id) {
    $polls = tpolls::i();
    $item = $polls->getitem($id);
    $item['votes'] = $polls->get_item_votes($id);
    return $item;
  }

} //class