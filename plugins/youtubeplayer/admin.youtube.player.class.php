<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminyoutubeplayer {
  
  public function getcontent() {
    $plugin = tyoutubeplayer::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::i();
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.template'] = $about['template'];
    $args->template = $plugin->template;
    $html = tadminhtml::i();
    return $html->adminform('[editor:template]', $args);
  }
  
  public function processform() {
    $plugin = tyoutubeplayer::i();
    $plugin->template = $_POST['template'];
    $plugin->save();
  }
  
}//class