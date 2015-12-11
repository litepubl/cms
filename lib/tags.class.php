<?php

class ttags extends tcommontags {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'tags';
    $this->basename = 'tags';
    $this->PermalinkIndex = 'tag';
    $this->postpropname = 'tags';
    $this->contents->table = 'tagscontent';
    $this->itemsposts->table = $this->table . 'items';
  }
  
  public function save() {
    parent::save();
    if (!$this->locked)  {
      ttagswidget::i()->expire();
    }
  }
  
}//class
