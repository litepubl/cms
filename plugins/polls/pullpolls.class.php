<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpullpolls extends tpullitems {
  
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
  
}//class