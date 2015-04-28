<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class admin_bootstrap_theme extends adminshopmenu  {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $shoptheme = shoptheme::i();
    $html = $this->inihtml();
    $lang = tlocal::inifile($this, '.admin.ini');
    $args = new targs();
    $args->sidebartype = tadminhtml::array2combo(array(
    'left' => $lang->left,
    'right' => $lang->right,
    ), $shoptheme->sidebar);
    
    $args->color = tadminhtml::array2combo($lang->ini['themenames'], $shoptheme->color);
    $args->formtitle = $lang->selectstyle;
    return $html->adminform('[combo=sidebartype] [combo=color]', $args);
  }
  
  public function processform() {
    $shoptheme = shoptheme::i();
    $sidebar = $_POST['sidebartype'];
    if (!in_array($sidebar, array('left', 'right'))) $sidebar = 'left';
    $shoptheme->sidebar = $sidebar;
    
    $color = $_POST['color'];
    $filename = litepublisher::$paths->themes . "shop/css/$color.min.css";
    if (file_exists($filename)) $shoptheme->color = $color;
    $shoptheme->save();
    
    ttheme::clearcache();
  }
  
}//class