<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl\plugins;
use litepubl;

class ttoptext extends tplugin {
  public $text;

  public static function i() {
    return getinstance(__class__);
  }

  public function beforecontent(tpost $post, &$content, &$cancel) {
    $sign = '[toptext]';
    if ($i = strpos($content, $sign)) {
      $this->text = substr($content, 0, $i);
      $content = substr($content, $i + strlen($sign));
    }
  }

  public function aftercontent(tpost $post) {
    if ($this->text) $post->filtered = $this->text . $post->filtered;
  }

} //class