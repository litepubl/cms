<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadminblackip {

  public static function i() {
    return getinstance(__class__);
  }

  public function getcontent() {
    $plugin = tblackip::i();
    $lang = tplugins::getlangabout('black-ip');
    $args = new targs();
    $args->ip = implode("\n", $plugin->ip);
    $args->words = implode("\n", $plugin->words);
    $args->ipstatus = tadminhtml::array2combo(tlocal::i()->ini['commentstatus'], $plugin->ipstatus);
    $args->wordstatus = tadminhtml::array2combo(tlocal::i()->ini['commentstatus'], $plugin->wordstatus);

    $tabs = new tabs();
    $tabs->add($lang->wordtitle, '[combo=wordstatus] [editor=words]');
    $tabs->add('IP', '[combo=ipstatus] [editor=ip]');

    $args->formtitle = $lang->formtitle;
    $html = tadminhtml::i();
    return $html->adminform($tabs->get() , $args);
  }

  public function processform() {
    $plugin = tblackip::i();
    $plugin->ipstatus = $_POST['ipstatus'];
    $plugin->wordstatus = $_POST['wordstatus'];
    $ip = str_replace(array(
      "\r\n",
      "\r"
    ) , "\n", $_POST['ip']);
    $ip = str_replace("\n\n", "\n", $ip);
    $plugin->ip = explode("\n", trim($ip));
    $words = str_replace(array(
      "\r\n",
      "\r"
    ) , "\n", $_POST['words']);
    $words = str_replace("\n\n", "\n", $words);
    $plugin->words = explode("\n", trim($words));
    $plugin->save();
  }

} //class