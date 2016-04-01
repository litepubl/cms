<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tpaths {
  public $home;
  public $lib;
  public $libinclude;
  public $storage;
  public $data;
  public $cache;
  public $backup;
  public $js;
  public $plugins;
  public $themes;
  public $files;

  public function __construct() {
    $this->home = dirname(__DIR__) . '/';
    $this->lib = __DIR__ . '/';
    $this->libinclude = $this->lib . 'include/';
    $this->languages = $this->lib . 'languages/';
    $this->storage = $this->home . 'storage/';
    $this->data = $this->storage . 'data/';
    $this->cache = $this->storage . 'cache/';
    $this->backup = $this->storage . 'backup/';
    $this->plugins = $this->home . 'plugins/';
    $this->themes = $this->home . 'themes/';
    $this->files = $this->home . 'files/';
    $this->js = $this->home . 'js/';
  }
}