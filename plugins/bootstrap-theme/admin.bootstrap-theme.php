<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class admin_bootstrap_theme extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $views = tviews::i();
    $html = $this->inihtml();
    $lang = tlocal::inifile($this, '.admin.ini');
    $args = new targs();

$mainsidebars = array(
    'left' => $lang->left,
    'right' => $lang->right,
);

foreach ($views->items as $it => $item) {
if (!isset($item['custom']['mainsidebar'])) continue;

      $result .= $html->h4($item['name']);
$result .=$theme->getinput('combo, "mainsidebar-$id",
 tadminhtml::array2combo($mainsidebars, $item['custom']['mainsidebar']), $lang->mainsidebar);

$result .=$theme->getinput('combo, "cssfile-$id",
tadminhtml::array2combo($lang->ini['subthemes'], $item['custom']['cssfile']), $lang->cssfile);
}

    $args->formtitle = $lang->selectstyle;
    return $html->adminform($result, $args);
  }
  
  public function processform() {
    $lang = tlocal::inifile($this, '.admin.ini');
    $views = tviews::i();
foreach ($views->items as $it => $item) {
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