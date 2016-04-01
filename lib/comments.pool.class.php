<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tcommentspool extends tpoolitems {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->basename = 'commentspool';
    $this->perpool = 50;
  }

  public function getitem($id) {
    return $this->getdb('posts')->getvalue($id, 'commentscount');
  }

  public function getlangcount($count) {
    $l = tlocal::i()->ini['comment'];
    switch ($count) {
      case 0:
        return $l[0];

      case 1:
        return $l[1];

      default:
        return sprintf($l[2], count);
    }
  }

  public function getlink($idpost, $tml) {
    return sprintf($tml, $this->getlangcount($this->get($idpost)));
  }

} //class