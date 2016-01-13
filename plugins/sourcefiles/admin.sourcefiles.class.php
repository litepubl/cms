<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadminsourcefiles  {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tsourcefiles::i();
$lang = tplugins::getnamelang(basename(dirname(__file__)));
    $html = tadminhtml::i();
    $args = new targs();
    $args->urlfile = '';
    $args->formtitle = $lang->title;
return $html->adminform('[text=urlfile]', $args);
  }
  
  public function processform() {
    $plugin = tsourcefiles::i();
$m = microtime(true);
$plugin->readzip(litepublisher::$paths->data . 'sourcefile.temp.zip');
echo round(microtime(true) - $m, 2);
return;
$url = trim($_POST['urlfile']);
if ($url && ($s = http::get($url))) {
$filename = litepublisher::$paths->data . 'sourcefile.temp.zip';
file_put_contents($filename, $s);
@chmod($filename, 0666);
$plugin->readzip($filename);
//unlink($filename);
}
}

}