<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tcustomtitle extends tplugin {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->data['post'] = '';
    $this->data['tag'] = '';
    $this->data['home'] = '';
    $this->data['archive'] = '';
  }

  public function ontitle(&$title) {
    $template = ttemplate::i();
    if ($template->context instanceof tpost) {
      $tml = $this->post;
    } elseif ($template->context instanceof tcommontags) {
      $tml = $this->tag;
    } elseif ($template->context instanceof thomepage) {
      $tml = $this->home;
    } elseif ($template->context instanceof tarchives) {
      $tml = $this->archive;
    } else {
      return false;
    }
    if ($tml == '') return;
    $title = $template->parsetitle($tml, $title);
    return true;
  }

} //class