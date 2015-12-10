<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadminviewsgroup extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  public function getcontent() {
    $views = tviews::i();
    $html = $this->html;
    $lang = tlocal::i('views');
    $args = new targs();

      $args->formtitle = $lang->viewposts;
      $result = $html->adminform(
      self::getcomboview($views->defaults['post'], 'postview') .
      '<input type="hidden" name="action" value="posts" />', $args);
      
      $args->formtitle = $lang->viewmenus;
      $result .= $html->adminform(
      self::getcomboview($views->defaults['menu'], 'menuview') .
      '<input type="hidden" name="action" value="menus" />', $args);
      
      $args->formtitle = $lang->themeviews;
      $view = tview::i();
      $list =    tfiler::getdir(litepublisher::$paths->themes);
      sort($list);
      $themes = array_combine($list, $list);
      $result .= $html->adminform(
      $html->getcombo('themeview', tadminhtml::array2combo($themes, $view->themename), $lang->themename) .
      '<input type="hidden" name="action" value="themes" />', $args);

    return $html->fixquote($result);
  }
  
  public function processform() {
      switch ($_POST['action']) {
        case 'posts':
        $posts = tposts::i();
        $idview = (int) $_POST['postview'];
        if (dbversion) {
          $posts->db->update("idview = '$idview'", 'id > 0');
        } else {
          foreach ($posts->items as $id => $item) {
            $post = tpost::i($id);
            $post->idview = $idview;
            $post->save();
            $post->free();
          }
        }
        break;
        
        case 'menus':
        $idview = (int) $_POST['menuview'];
        $menus = tmenus::i();
        foreach ($menus->items as $id => $item) {
          $menu = tmenu::i($id);
          $menu->idview = $idview;
          $menu->save();
        }
        break;
        
        case 'themes':
        $themename = $_POST['themeview'];
        $views = tviews::i();
        $views->lock();
        foreach ($views->items as $id => $item) {
          $view = tview::i($id);
          $view->themename = $themename;
          $view->save();
        }
        $views->unlock();
        break;
      }
}

}//class