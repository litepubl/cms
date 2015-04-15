<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class themleditplugin extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function gethead() {
    $url = litepublisher::$site->files . '/plugins/' . tplugins::getname(__file__);
    $about = tplugins::getabout(tplugins::getname(__file__));
    $result = '<link rel="stylesheet" href="' . $url . '/ed.css" type="text/css" />';
    $result .= '<script type="text/javascript">';
    $result .= '    var URLpromt = "' . $about['urlpromt'] . '";';
    $result .= ' var IMGpromt = "' . $about['imgpromt'] . '";';
    $result .= '</script>';
    $result .= '<script type="text/javascript" src="'. $url . '/ed.js"></script>';
    return $result;
  }
  
  public function install() {
    $admin = tadminmenus::i();
    $admin->heads .= $this->gethead();
    $admin->save();
  }
  
  public function uninstall() {
    $head = $this->gethead();
    $admin = tadminmenus::i();
    $admin->heads = str_replace($head, '', $admin->heads);
    $admin->save();
  }
  
}//class