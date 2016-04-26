<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

class CacheMemcache extends BaseCache
{
    protected$memcache;
   protected$prefix;
    protected $revision;
    protected $revisionKey;

    public function __construct($memcache, $lifetime, $prefix)
 {
        $this->memcache =  $memcache;
        $this->lifetime = $lifetime;
        $this->prefix =  $prefix . ':cache:';
        $this->revision = 0;
        $this->revisionKey = 'cache_revision';
$this->items = [];
            $this->getRevision();
    }

    public function getPrefix() {
        return $this->prefix . $this->revision . '.';
    }

    public function getRevision() {
        return $this->revision = (int)$this->memcache->get($this->prefix . $this->revisionKey);
    }

    public function clear() {
        $this->revision++;
        $this->memcache->set($this->prefix . $this->revisionKey, "$this->revision", false, $this->lifetime);
$this->items = [];
    }

    public function setString($filename, $str) {
$this->items[$filename] = $str;
        $this->memcache->set($this->getPrefix() . $filename, $str, false, $this->lifetime);
    }

   public function getString($filename) {
if (array_key_exists($filename, $this->items)) {
return $this->items[$filename];
}

        return $this->memcache->get($this->getPrefix() . $filename);
    }

    public function delete($filename) {
unset($this->items[$filename]);
        $this->memcache->delete($this->getPrefix() . $filename);
    }

    public function exists($filename) {
if (parent::exists($filename)) {
return $this->items[$filename] !== false;
}

return $this->items[$filename] = $this->getString($filename);
    }

}