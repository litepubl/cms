<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\core;

class Paths
{
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

    public function __construct()
    {
        $this->home = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
        $this->lib = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $this->libinclude = $this->lib . 'include' . DIRECTORY_SEPARATOR;
        $this->languages = $this->lib . 'languages' . DIRECTORY_SEPARATOR;
        $this->storage = $this->home . 'storage' . DIRECTORY_SEPARATOR;
        $this->data = $this->storage . 'data' . DIRECTORY_SEPARATOR;
        $this->cache = $this->storage . 'cache' . DIRECTORY_SEPARATOR;
        $this->backup = $this->storage . 'backup' . DIRECTORY_SEPARATOR;
        $this->plugins = $this->home . 'plugins' . DIRECTORY_SEPARATOR;
        $this->themes = $this->home . 'themes' . DIRECTORY_SEPARATOR;
        $this->files = $this->home . 'files' . DIRECTORY_SEPARATOR;
        $this->js = $this->home . 'js' . DIRECTORY_SEPARATOR;
    }
}
