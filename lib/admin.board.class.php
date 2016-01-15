<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadminboard extends tevents implements itemplate {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->cache = false;
  }

  public function load() {
    return true;
  }
  public function save() {
    return true;
  }

  public function request($id) {
    if ($s = tguard::checkattack()) return $s;
    if (!litepublisher::$options->user) {
      return litepublisher::$urlmap->redir('/admin/login/' . litepublisher::$site->q . 'backurl=' . urlencode(litepublisher::$urlmap->url));
    }

    if (!litepublisher::$options->hasgroup('editor')) {
      $url = tusergroups::i()->gethome(litepublisher::$options->group);
      if ($url == '/admin/') {
        return 403;
      }

      return litepublisher::$urlmap->redir($url);
    }

    tlocal::usefile('admin');
  }

  public function gethead() {
    $editor = tposteditor::i();
    return $editor->gethead();
  }

  public function gettitle() {
    return tlocal::get('common', 'board');
  }

  public function getkeywords() {
    return '';
  }

  public function getdescription() {
    return '';
  }

  public function getidview() {
    return tviews::i()->defaults['admin'];
  }

  public function setidview($id) {
  }

  public function getcont() {
    $editor = tposteditor::i();
    return $editor->getexternal();
  }

  public function gethtml($name = '') {
    if (!$name) {
      $name = 'login';
    }

    $result = tadminhtml::i();
    $result->section = $name;
    tlocal::admin($name);
    return $result;
  }

  public function getlang() {
    return tlocal::admin('login');
  }

} //class