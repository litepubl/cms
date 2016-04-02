<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tmarkdownplugin extends tplugin {
  public $parser;

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->data['deletep'] = false;
    litepubl::$classes->include_file(litepubl::$paths->plugins . 'markdown' . DIRECTORY_SEPARATOR . 'MarkdownInterface.php');
    litepubl::$classes->include_file(litepubl::$paths->plugins . 'markdown' . DIRECTORY_SEPARATOR . 'Markdown.php');
    $this->parser = new Michelf\Markdown();
  }

  public function filter(&$content) {
    if ($this->deletep) $content = str_replace('_', '&#95;', $content);
    $content = $this->parser->transform($content);
    if ($this->deletep) $content = strtr($content, array(
      '<p>' => '',
      '</p>' => '',
      '&#95;' => '_'
    ));
  }

  public function install() {
    $filter = tcontentfilter::i();
    $filter->lock();
    $filter->onsimplefilter = $this->filter;
    $filter->oncomment = $this->filter;
    $filter->unlock();
  }

  public function uninstall() {
    $filter = tcontentfilter::i();
    $filter->unbind($this);
  }

} //class