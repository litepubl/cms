<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class treguser extends titems {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'regservices/users';
    //$this->table = 'regservices';
    $this->table = 'ulogin';
  }

  public function add($id, $service, $uid) {
    if (($id == 0) || ($service == '') || ($uid == '')) return;
    $this->db->insert(array(
      'id' => $id,
      'service' => $service,
      'uid' => $uid
    ));

    $this->added($id, $service);
  }

  public function find($service, $uid) {
    return $this->db->findid('service = ' . dbquote($service) . ' and uid = ' . dbquote($uid));
  }

} //class