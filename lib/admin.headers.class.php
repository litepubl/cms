<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class tadminheaders extends tadminmenu {

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  public function getcontent() {
    $result = '';
    $views = tviews::i();
    $html = $this->html;
    $lang = tlocal::i('views');
    $args = new targs();

    $tabs = new tabs($this->admintheme);
    $args->heads = ttemplate::i()->heads;
    $tabs->add($lang->headstitle, '[editor=heads]');

    $args->adminheads = tadminmenus::i()->heads;
    $tabs->add($lang->admin, '[editor=adminheads]');

    $ajax = tajaxposteditor::i();
    $args->ajaxvisual = $ajax->ajaxvisual;
    $args->visual = $ajax->visual;
    $args->show_file_perm = litepublisher::$options->show_file_perm;
    $tabs->add($lang->posteditor, '[checkbox=show_file_perm] [checkbox=ajaxvisual] [text=visual]');

    $args->formtitle = $lang->headstitle;
    return $html->adminform($tabs->get() , $args);
  }

  public function processform() {

    $template = ttemplate::i();
    $template->heads = $_POST['heads'];
    $template->save();

    $adminmenus = tadminmenus::i();
    $adminmenus->heads = $_POST['adminheads'];
    $adminmenus->save();

    $ajax = tajaxposteditor::i();
    $ajax->lock();
    $ajax->ajaxvisual = isset($_POST['ajaxvisual']);
    $ajax->visual = trim($_POST['visual']);
    $ajax->unlock();

    litepublisher::$options->show_file_perm = isset($_POST['show_file_perm']);
  }

} //class