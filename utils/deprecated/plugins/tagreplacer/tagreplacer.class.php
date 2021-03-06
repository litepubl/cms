<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class ttagreplacer extends titems {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->basename = 'plugins' . DIRECTORY_SEPARATOR . strtolower(get_class($this));
  }

  public function themeparsed(ttheme $theme) {
    foreach ($this->items as $item) {
      $where = $item['where'];
      if (isset($theme->templates[$where]) && (false == strpos($theme->templates[$where], $item['replace']))) {
        $theme->templates[$where] = str_replace($item['search'], $item['replace'], $theme->templates[$where]);
      }
    }
  }

} //class