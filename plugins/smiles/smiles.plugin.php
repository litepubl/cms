<?php

class tsmiles extends  tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function filter(&$content) {
    $content = str_replace(array(':)', ';)'),
    sprintf('<img src="%s/plugins/%s/1.gif" alt="smile" title="smile" />', litepublisher::$site->files, basename(dirname(__file__))),
    $content);
  }
  
}