<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class admin_bootstrap_header extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function gethead() {
$result = parent::gethead();

$result .= "<script type=\"text/javascript\">ltoptions.header_tml ='" . file_get_contents(dirname(__file__) . '/resource/header.tml')) . "';</script>";
$result .= '<script type="text/javascript" src="$site.files/js/plugins/filereader.min.js"></script>';
$result .= '<script type="text/javascript" src="$site.files/plugins/bootstrap-theme/resource/header.min.js"></script>';

return $result;
}
  
  public function getcontent() {
    $result = '';
    $views = tviews::i();
    $theme = tview::i($views->defaults['admin'])->theme;
    $html = $this->inihtml();
    $lang = tlocal::inifile($this, '.admin.ini');
    $args = new targs();
    
    $mainsidebars = array(
    'left' => $lang->left,
    'right' => $lang->right,
    );
    
    foreach ($views->items as $id => $item) {
      if (!isset($item['custom']['mainsidebar'])) continue;
      
      $result .= $html->h4($item['name']);
      $result .=$theme->getinput('combo', "mainsidebar-$id",
      tadminhtml::array2combo($mainsidebars, $item['custom']['mainsidebar']), $lang->mainsidebar);
      
      $result .=$theme->getinput('combo', "cssfile-$id",
      tadminhtml::array2combo($lang->ini['subthemes'], $item['custom']['cssfile']), $lang->cssfile);
      
      $result .= '<hr>';
    }
    
    $args->formtitle = $lang->customizeview;
    return $html->adminform($result, $args);
  }
  
  public function processform() {
    $lang = tlocal::inifile($this, '.admin.ini');
    $views = tviews::i();
    foreach ($views->items as $id => $item) {
      if (!isset($item['custom']['mainsidebar'])) continue;
      
      $sidebar = $_POST["mainsidebar-$id"];
      if (!in_array($sidebar, array('left', 'right'))) $sidebar = 'left';
      $views->items[$id]['custom']['mainsidebar'] = $sidebar;
      
      $cssfile = $_POST["cssfile-$id"];
      if (!isset($lang->ini['subthemes'][$cssfile])) $cssfile = 'default';
      $views->items[$id]['custom']['cssfile'] = $cssfile;
    }
    
    $views->save();
    ttheme::clearcache();
  }
  
}//class