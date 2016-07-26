<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\core;

class CacheMemcache extends BaseCache
{
    protected $memcache;
    protected $prefix;
    protected $revision;
    protected $revisionKey;

    public function __construct(\Memcache $memcache, int $lifetime, int $prefix)
    {
        $this->memcache = $memcache;
        $this->lifetime = $lifetime;
        $this->prefix = $prefix . ':cache:';
        $this->revision = 0;
        $this->revisionKey = 'cache_revision';
        $this->getRevision();
    }

    public function getPrefix(): string
    {
        return $this->prefix . $this->revision . '.';
    }

    public function getRevision(): int
    {
        return $this->revision = (int)$this->memcache->get($this->prefix . $this->revisionKey);
    }

    public function clear()
    {
        $this->revision++;
        $this->memcache->set($this->prefix . $this->revisionKey, "$this->revision", false, $this->lifetime);
        parent::clear();
    }

    public function setString(string $filename, string $str)
    {
        $this->items[$filename] = $str;
        $this->memcache->set($this->getPrefix() . $filename, $str, false, $this->lifetime);
    }

    public function getString(string $filename): string
    {
        if (array_key_exists($filename, $this->items)) {
            return $this->items[$filename];
        }

        return $this->memcache->get($this->getPrefix() . $filename);
    }

    public function delete(string $filename)
    {
        unset($this->items[$filename]);
        $this->memcache->delete($this->getPrefix() . $filename);
    }

    public function exists(string $filename)
    {
        if (parent::exists($filename)) {
            return $this->items[$filename] !== false;
        }

        return $this->items[$filename] = $this->getString($filename);
    }
}
