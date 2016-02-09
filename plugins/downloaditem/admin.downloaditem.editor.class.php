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
    if ($this->idpost == 0) {
      return parent::gettitle();
    } else {
      return tlocal::admin('downloaditems')->editor;
    }
  }

public function gettabs($post = null) {
$post = $this->getvarpost($post);
$args = new targs();
$this->getargstab($post, $args);

    $lang = tlocal::admin('downloaditems');
    $lang->addsearch('downloaditem', 'editor');

$admintheme = $this->admintheme;

//add tab before cats
$tml = strtr($admintheme->templates['posteditor.tabs'], array(
'[tab=categories]' => '[tab=downloaditem] [tab=categories]',
'[tabpanel=categories]' => '[tabpanel=downloaditem{
[combo=type]
[text=downloadurl]
[text=authorurl]
[text=authorname]
[text=version]
}] [tabpanel=categories]',
));

return $admintheme->parsearg($tml, $args);
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

}//class