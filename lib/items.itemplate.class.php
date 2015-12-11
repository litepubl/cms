<?php

class titems_itemplate extends titems {
  
  protected function create() {
    parent::create();
    $this->data['idview'] = 1;
    $this->data['keywords'] = '';
    $this->data['description'] = '';
    $this->data['head'] = '';
  }
  
  public function gethead() {
    return $this->data['head'];
  }
  
  public function getkeywords() {
    return $this->data['keywords'];
  }
  
  public function getdescription() {
    return $this->data['description'];
  }
  
  public function getidview() {
    return $this->data['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->data['idview']) {
      $this->data['idview'] = $id;
      $this->save();
    }
  }
  
  public function getview() {
    return tview::getview($this);
  }
  
}//class