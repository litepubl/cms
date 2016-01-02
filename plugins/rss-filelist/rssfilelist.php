<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class trssfilelist extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function beforepost($id, &$content) {
    $post = tpost::i($id);
    if (count($post->files) > 0) {
      $theme = $post->theme;
      $image = $theme->templates['content.post.filelist.image'];
      $theme->templates['content.post.filelist.image'] = str_replace('href="$link"',
      'href="$post.link#!prettyPhoto[gallery-$post.id]/$typeindex/"', $image);
      $content .= $post->filelist;
      $theme->templates['content.post.filelist.image'] = $image;
    }
  }
  
}//class