<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tcodedocplugin extends tplugin {
  private $post;

  public static function i() {
    return getinstance(__class__);
  }

  public function filterpost($post, &$content, &$cancel) {
    if (preg_match('/^(classname|interface)\s*[=:]\s*\w*+/i', $content, $m)) {
      $this->post = $post;
      $filter = tcodedocfilter::i();
      $content = $filter->filter($post, $content, $m[1]);
      $cancel = true;
    }
  }

  public function afterfilter($post, &$content, &$cancel) {
    if ($post == $this->post) {
      $post->filtered = $content;
      $cancel = true;
    }
  }

  public function postdeleted($id) {
    litepubl::$db->table = 'codedoc';
    litepubl::$db->delete("id = $id");
  }
} //class