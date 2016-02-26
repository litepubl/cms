<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class tadminuseroptions extends tadminmenu {

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function getcontent() {
    $result = '';
    $html = $this->html;
    $lang = tlocal::i('users');
    $args = new targs();
    $args->formtitle = $lang->useroptions;

    $pages = tuserpages::i();
    $args->createpage = $pages->createpage;

    $linkgen = tlinkgenerator::i();
    $args->linkschema = $linkgen->data['user'];

    $groups = tusergroups::i();
    $args->defaulthome = $groups->defaulthome;

    return $html->adminform('[checkbox=createpage]
    [text=linkschema]
    [text=defaulthome]' . $html->h4->defaults . tadmingroups::getgroups($groups->defaults) , $args);
  }

  public function processform() {
    $pages = tuserpages::i();
    $pages->createpage = isset($_POST['createpage']);
    $pages->save();

    $groups = tusergroups::i();
    $groups->defaults = tadminhtml::check2array('idgroup-');
    $groups->defaulthome = trim($_POST['defaulthome']);
    $groups->save();

    $linkgen = tlinkgenerator::i();
    $linkgen->data['user'] = $_POST['linkschema'];
    $linkgen->save();
  }

} //class