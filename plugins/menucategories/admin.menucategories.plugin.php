<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincategoriesmenu  {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tcategoriesmenu::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::i();
    $args->cats = tposteditor::getcategories($plugin->exitems);
    $args->formtitle = $about['formtitle'];
    //    $args->data['$lang.before'] = $about['before'];
    
    $html = tadminhtml::i();
    return $html->adminform('$cats', $args);
  }
  
  public function processform() {
    $plugin = tcategoriesmenu::i();
    $plugin->exitems = tadminhtml::check2array('category-');
    $plugin->save();
  }
  
}//class