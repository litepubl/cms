<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class tdownloaditemeditor extends tposteditor {

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function gettitle() {
    $lang = tlocal::admin('downloaditems');
    $lang->addsearch('downloaditems', 'downloaditem', 'editor');

    if ($this->idpost == 0) {
      return parent::gettitle();
    } else {
      return tlocal::admin('downloaditems')->editor;
    }
  }

  public function gettabstemplate() {
    $admintheme = $this->admintheme;
    return strtr($admintheme->templates['tabs'], array(
      '$id' => 'tabs',
      '$tab' => '[tab=downloaditem]' . $admintheme->templates['posteditor.tabs.tabs'],
      '$panel' => '[tabpanel=downloaditem{
[combo=type]
[text=downloadurl]
[text=authorurl]
[text=authorname]
[text=version]
}]' . $admintheme->templates['posteditor.tabs.panels'],
    ));
  }

  public function getargstab(tpost $post, targs $args) {
    parent::getargstab($post, $args);

    $args->downloadurl = $post->downloadurl;
    $args->authorname = $post->authorname;
    $args->authorurl = $post->authorurl;
    $args->version = $post->version;

    $types = array(
      'theme' => tlocal::get('downloaditem', 'theme') ,
      'plugin' => tlocal::get('downloaditem', 'plugin')
    );

    $args->type = tadminhtml::array2combo($types, $post->type);
  }

  public function newpost() {
    return new tdownloaditem();
  }

  public function processtab(tpost $post) {
    parent::processtab($post);

    extract($_POST, EXTR_SKIP);
    $post->version = $version;
    $post->type = $type;
    $post->downloadurl = $downloadurl;
    $post->authorname = $authorname;
    $post->authorurl = $authorurl;
  }

} //class