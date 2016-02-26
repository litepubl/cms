<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class tadminsourcefiles {

  public static function i() {
    return getinstance(__class__);
  }

  public function getcontent() {
    $plugin = tsourcefiles::i();
    $lang = tplugins::getnamelang(basename(dirname(__file__)));
    $html = tadminhtml::i();
    $args = new targs();
    $args->zipurl = $plugin->zipurl;
    $args->formtitle = $lang->title;
    return $html->adminform('[text=zipurl]', $args);
  }

  public function processform() {
    $plugin = tsourcefiles::i();
    $m = microtime(true);
    $url = trim($_POST['zipurl']);
    if ($url && ($s = http::get($url))) {
      $plugin->data['zipurl'] = $url;
      $plugin->save();
      set_time_limit(120);
      $filename = litepublisher::$paths->data . 'sourcefile.temp.zip';
      file_put_contents($filename, $s);
      @chmod($filename, 0666);
      $plugin->clear();
      $plugin->readzip($filename);
      unlink($filename);
      return sprintf('<h4>Processed  by %f seconds</h4>', round(microtime(true) - $m, 2));
    }
  }

}