<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminitemsreplacer implements iadmin{
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function  gethead() {
    return tuitabs::gethead();
  }
  
  public function getcontent() {
    $result = '';
    $plugin = titemsreplacer ::i();
    $views = tviews::i();
    $html = tadminhtml::i();
    $args = targs::i();
    $lang = tplugins::getlangabout(__file__);
    $adminurl = tadminhtml::getadminlink('/admin/plugins/', 'plugin=' . basename(dirname(__file__)));
    
    if (!empty($_GET['id'])) {
      $id = (int) $_GET['id'];
      if (isset($plugin->items[$id])) {
        $args->formtitle = sprintf($lang->formtitle, $views->items[$id]['name']);
        $tabs = new tuitabs();
        
        $tabs->add($lang->add, $html->getinput('text',
        'addtag', '', $lang->addtag) .
        $html->getinput('editor',
        'addreplace', '', $lang->replace) );
        
        $i = 0;
        foreach ($plugin->items[$id] as $tag => $replace) {
          $tabs->add($tag,
          $html->getinput('editor',
          "replace-$i", tadminhtml::specchars($replace), $lang->replace) );
          $i++;
        }
        
        $result .= $html->adminform($tabs->get(), $args);
      }
    }
    
    $result .= "<h4>$lang->viewlist</h4><ul>";
    foreach (array_keys($plugin->items) as $id) {
      $name= $views->items[$id]['name'];
      $result .= "<li><a href='$adminurl&id=$id'>$name</a></li>";
    }
    $result .= '</ul>';
    
    $form = "<h3>$lang->addview</h3>
    <form name='form' action='$adminurl&action=add' method='post' >
    " . $html->getinput('text', 'viewname', '', $lang->viewname) . "
    <p><input type='submit' name='submitbutton' id='idsubmitbutton' value='$lang->add' /></p>
    </form>";
    
    $result .= $form;
    return $result;
  }
  
  public function processform() {
    $plugin = titemsreplacer ::i();
    
    if (!empty($_GET['id'])) {
      $id = (int) $_GET['id'];
      if (isset($plugin->items[$id])) {
        $plugin->lock();
        $i = 0;
        foreach ($plugin->items[$id] as $tag => $replace) {
          $k = "replace-$i";
          if (!isset($_POST[$k])) continue;
          $v = trim($_POST[$k]);
          if ($v) {
            $plugin->items[$id][$tag] = $v;
          } else {
            unset($plugin->items[$id][$tag]);
          }
          $i++;
        }
        
        if (!empty($_POST['addtag'])) {
          $tag = trim($_POST['addtag']);
          $theme = tview::i(tviews::i()->defaults['admin'])->theme;
          if (isset($theme->templates[$tag])) {
            $plugin->items[$id][$tag] = trim($_POST['addreplace']);
          }
        }
        
        $plugin->unlock();
      }
    }
    
    if (isset($_GET['action']) && ('add' == $_GET['action'])) {
      $views = tviews::i();
      $view = new tviewthemereplacer();
      $view->name = trim($_POST['viewname']);
      $id = $views->addview($view);
      $plugin->add($id);
      $view->themename = tview::i(1)->themename;
      $adminurl = tadminhtml::getadminlink('/admin/plugins/', 'plugin=' . basename(dirname(__file__)));
      return litepublisher::$urlmap->redir("$adminurl&id=$id");
    }
    
    ttheme::clearcache();
    return '';
  }
  
}//class