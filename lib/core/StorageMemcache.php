<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\core;

class StorageMemcache extends Storage
{
    public $memcache;

    public function __construct()
    {
        parent::__construct();
        $this->memcache = $this->getApp()->memcache;
    }

    public function loadFile($filename)
    {
        if ($s = $this->memcache->get($filename)) {
            return $s;
        }

        if ($s = parent::loadFile($filename)) {
            $this->memcache->set($filename, $s, false, 3600);
            return $s;
        }

        return false;
    }

    public function saveFile($filename, $content)
    {
        $this->memcache->set($filename, $content, false, 3600);
        return parent::saveFile($filename, $content);
    }

    public function delete($filename)
    {
        parent::delete($filename);
        $this->memcache->delete($filename);
    }
}
