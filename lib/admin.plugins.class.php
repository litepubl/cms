<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tadminplugins extends tadminmenu {
  private $names;

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  protected function create() {
    parent::create();
    $this->names = tfiler::getdir(litepublisher::$paths->plugins);
    sort($this->names);
  }

  public function getpluginsmenu() {
    $result = '';
    $link = tadminhtml::getadminlink($this->url, 'plugin=');
    $plugins = tplugins::i();
    foreach ($this->names as $name) {
      $about = tplugins::getabout($name);
      if (isset($plugins->items[$name]) && !empty($about['adminclassname'])) {
        $result.= sprintf('<li><a href="%s%s">%s</a></li>', $link, $name, $about['name']);
      }
    }

    return sprintf('<ul>%s</ul>', $result);
  }

  public function gethead() {
    $result = parent::gethead();
    if (!empty($_GET['plugin'])) {
      $name = $_GET['plugin'];
      if (in_array($name, $this->names)) {
        if ($admin = $this->getadminplugin($name)) {
          if (method_exists($admin, 'gethead')) $result.= $admin->gethead();
        }
      }
    }
    return $result;
  }

  public function getcontent() {
    $result = $this->getpluginsmenu();
    $admintheme = $this->view->admintheme;
    $lang = $this->lang;
    $plugins = tplugins::i();

    if (empty($_GET['plugin'])) {
      $result.= $admintheme->parse($admintheme->templates['help.plugins']);

      $tb = new tablebuilder();
      $tb->setstruct(array(
        $tb->namecheck() ,

        array(
          $lang->name,
          '$short'
        ) ,

        array(
          'right',
          $lang->version,
          '$version'
        ) ,

        array(
          $lang->description,
          '$description'
        ) ,
      ));

      $body = '';
      $args = $tb->args;
      foreach ($this->names as $name) {
        if (in_array($name, $plugins->deprecated)) continue;

        $about = tplugins::getabout($name);
        $args->add($about);
        $args->name = $name;
        $args->checked = isset($plugins->items[$name]);
        $args->short = $about['name'];
        $body.= $admintheme->parsearg($tb->body, $args);
      }

      $form = new adminform();
      $form->title = $lang->formhead;
      $form->body = $admintheme->gettable($tb->head, $body);
      $form->submit = 'update';

      //no need to parse form
      $result.= $form->gettml();
    } else {
      $name = $_GET['plugin'];
      if (!in_array($name, $this->names)) return $this->notfound;
      if ($admin = $this->getadminplugin($name)) {
        $result.= $admin->getcontent();
      }
    }

    return $result;
  }

  public function processform() {
    if (!isset($_GET['plugin'])) {
      $list = array_keys($_POST);
      array_pop($list);
      $plugins = tplugins::i();
      try {
        $plugins->update($list);
      }
      catch(Exception $e) {
        litepublisher::$options->handexception($e);
      }
      $result = $this->view->theme->h(tlocal::i()->updated);
    } else {
      $name = $_GET['plugin'];
      if (!in_array($name, $this->names)) return $this->notfound;
      if ($admin = $this->getadminplugin($name)) {
        $result = $admin->processform();
      }
    }

    litepublisher::$urlmap->clearcache();
    return $result;
  }

  private function getadminplugin($name) {
    $about = tplugins::getabout($name);
    if (empty($about['adminclassname'])) return false;
    $class = $about['adminclassname'];
    if (!class_exists($class)) litepublisher::$classes->include_file(litepublisher::$paths->plugins . $name . DIRECTORY_SEPARATOR . $about['adminfilename']);
    return getinstance($class);
  }

} //class