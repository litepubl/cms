<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminmarkdownplugin {

  public static function i() {
    return getinstance(__class__);
  }

  public function getcontent() {  
$plugin = tmarkdownplugin::i();

    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::i();
    $html = tadminhtml::i();

      $args->formtitle = $about['name'];
      $args->data['$lang.deletep'] = $about['deletep'];
      $args->deletep = $plugin->deletep;
return $html->adminform('[checkbox=deletep]', $args);
}

  public function processform() {
$plugin = tmarkdownplugin::i();
$plugin->deletep = isset($_POST['deletep']);
$plugin->save();
}

}//class
