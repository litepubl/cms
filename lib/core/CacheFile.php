<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\core;

class CacheFile extends BaseCache
{
    protected $dir;
    protected $timeOffset;

    public function __construct(string $dir, int $lifetime, int $timeOffset)
    {
        parent::__construct();
        $this->dir = $dir;
        $this->timeOffset = $timeOffset;
        $this->lifetime = $lifetime - $timeOffset;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function setString(string $filename, string $str)
    {
        $this->items[$filename] = $str;
        $fn = $this->getdir() . $filename;
        file_put_contents($fn, $str);
        @chmod($fn, 0666);
    }

    public function getString(string $filename): string
    {
        if (array_key_exists($filename, $this->items)) {
            return $this->items[$filename];
        }

        $fn = $this->getdir() . $filename;
        if (file_exists($fn) && (filemtime($fn) + $this->lifetime >= time())) {
            return $this->items[$filename] = file_get_contents($fn);
        }

        return false;
    }

    public function delete(string $filename)
    {
        unset($this->items[$filename]);
        $fn = $this->getdir() . $filename;
        if (file_exists($fn)) {
            unlink($fn);
        }
    }

    public function exists(string $filename): bool
    {
        return array_key_exists($filename, $this->items) || (file_exists($this->getdir() . $filename) && (filemtime($this->getDir() . $filename) + $this->lifetime >= time()));
    }

    public function setLifetime(int $value)
    {
        $this->lifetime = $value - $this->timeOffset;
    }

    public function clear()
    {
        $this->clearDir($path = $this->getdir());
        parent::clear();
    }

    public function clearDir(string $dir)
    {
        if ($h = @opendir($path)) {
            while (false !== ($filename = @readdir($h))) {
                if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) {
                    continue;
                }

                $file = $path . $filename;
                if (is_dir($file)) {
                    $this->clearDir($file . DIRECTORY_SEPARATOR);
                    unlink($file);
                } else {
                    unlink($file);
                }
            }
            closedir($h);
        }
    }

    public function includePhp(string $filename)
    {
        $fn = $this->getDir() . $filename;
        if (file_exists($fn) && (filemtime($fn) + $this->lifetime >= time())) {
            if (defined('HHVM_VERSION')) {
                // XXX: workaround for https://github.com/facebook/hhvm/issues/1447
                eval('?>' . file_get_contents($fn));
            } else {
                include $fn;
            }
            return true;
        }

        return false;
    }
}
