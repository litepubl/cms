<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadminlocalmerger extends tadminmenu {
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function getcontent() {
    $merger = tlocalmerger::i();
    $tabs = new tabs($this->admintheme);
    $html = $this->html;
    $lang = tlocal::i('options');
    $args = targs::i();

    foreach ($merger->items as $section => $items) {
      $tab = new tabs($this->admintheme);
      $tab->add($lang->files, $html->getinput('editor', $section . '_files', tadminhtml::specchars(implode("\n", $items['files'])) , $lang->files));
      foreach ($items['texts'] as $key => $text) {
        $tab->add($key, $html->getinput('editor', $section . '_text_' . $key, tadminhtml::specchars($text) , $key));
      }

      $tabs->add($section, $tab->get());
    }

    $tabs->add('HTML', $html->getinput('editor', 'adminhtml_files', tadminhtml::specchars(implode("\n", $merger->html)) , $lang->files));

    $args->formtitle = $lang->optionslocal;
    $args->dateformat = litepublisher::$options->dateformat;
    $dirs = tfiler::getdir(litepublisher::$paths->languages);
    $args->language = tadminhtml::array2combo(array_combine($dirs, $dirs) , litepublisher::$options->language);
    $zones = timezone_identifiers_list();
    $args->timezone = tadminhtml::array2combo(array_combine($zones, $zones) , litepublisher::$options->timezone);

    return $html->adminform('[text=dateformat]
    [combo=language]
    [combo=timezone]' . $tabs->get() , $args);
  }

  public function processform() {
    litepublisher::$options->dateformat = $_POST['dateformat'];
    litepublisher::$options->language = $_POST['language'];
    if (litepublisher::$options->timezone != $_POST['timezone']) {
      litepublisher::$options->timezone = $_POST['timezone'];
      $archives = tarchives::i();
      turlmap::unsub($archives);
      $archives->PostsChanged();
    }

    $merger = tlocalmerger::i();
    $merger->lock();
    //$merger->items = array();
    //$merger->install();
    foreach (array_keys($merger->items) as $name) {
      $keys = array_keys($merger->items[$name]['texts']);
      $merger->setfiles($name, $_POST[$name . '_files']);
      foreach ($keys as $key) {
        $merger->addtext($name, $key, $_POST[$name . '_text_' . $key]);
      }
    }

    $merger->html = explode("\n", trim($_POST['adminhtml_files']));
    foreach ($merger->html as $i => $filename) $merger->html[$i] = trim($filename);
    $merger->unlock();
  }

} //class