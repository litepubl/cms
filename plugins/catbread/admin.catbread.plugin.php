<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class admincatbread implements iadmin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = catbread::i();
    $lang = tplugins::getnamelang('catbread');
    $html= tadminhtml::i();
    $args = new targs();
    $args->add($plugin->tml);
    $args->showhome = $plugin->showhome;
    $args->showchilds = $plugin->showchilds;
    $args->showsimilar = $plugin->showsimilar;
    
    $lang->addsearch('sortnametags');
    $sort = array(
    'title' => $lang->title,
    'itemscount' => $lang->count,
    'customorder' => $lang->customorder,
    );
    
    $args->sort = tadminhtml::array2combo($sort, $plugin->childsortname);
    
    $pos = array(
    'top' => $lang->top,
    'before' => $lang->before,
    'after' => $lang->after,
    );
    
    $args->breadpos = tadminhtml::array2combo($pos, $plugin->breadpos);
    $args->similarpos = tadminhtml::array2combo($pos, $plugin->similarpos);
    
    $args->formtitle = $lang->formtitle;
    return $html->adminform('
    [checkbox=showhome]
    
    [combo=breadpos]
    [text=item]
    [text=active]
    [text=child]
    [editor=items]
    [editor=container]
    
    [checkbox=showchilds]
    [combo=sort]
    [text=childitem]
    [text=childsubitems]
    [editor=childitems]
    
    [checkbox=showsimilar]
    [combo=similarpos]
    [text=similaritem]
    [text=similaritems]
    ', $args);
  }
  
  public function processform()  {
    extract($_POST, EXTR_SKIP);
    $plugin = catbread::i();
    $plugin->showhome = isset($showchilds);
    $plugin->showchilds = isset($showchilds);
    $plugin->showsimilar = isset($showsimilar);
    $plugin->childsortname = $sort;
    $plugin->breadpos = $breadpos;
    $plugin->similarpos = $similarpos;
    foreach ($plugin->tml as $k => $v) {
      $plugin->tml[$k] = trim($_POST[$k]);
    }
    
    $plugin->save();
    return '';
  }
  
}//class