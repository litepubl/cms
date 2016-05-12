<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

class CacheFile extends BaseCache
{
protected $dir;
protected $timeOffset;

public function __construct($dir, $lifetime, $timeOffset)
 {
$this->dir = $dir;
$this->timeOffset = $timeOffset;
$this->lifetime = $lifetime - $timeOffset;
$this->items = [];
}

    public function getDir() {
        return  $this->dir;
    }

    public function setString($filename, $str) {
$this->items[$filename] = $str;
        $fn = $this->getdir() . $filename;
        file_put_contents($fn, $str);
        @chmod($fn, 0666);
    }

    public function getString($filename) {
if (array_key_exists($filename, $this->items)) {
return $this->items[$filename];
}

        $fn = $this->getdir() . $filename;
        if (file_exists($fn) && (filemtime($fn) + $this->lifetime >= time())) {
            return $this->items[$filename] = file_get_contents($fn);
        }

        return false;
    }

    public function delete($filename) {
unset($this->items[$filename]);
        $fn = $this->getdir() . $filename;
        if (file_exists($fn)) {
            unlink($fn);
        }
    }

    public function exists($filename) {
        return array_key_exists($filename, $this->items) ||
( file_exists($this->getdir() . $filename) && (filemtime($this->getDir() . $filename) + $this->lifetime >= time()));
    }

public function setLifetime($value)
{
$this->lifetime = $value - $this->timeOffset;
}

    public function clear() {
$this->items = [];
$this->clearDir(       $path = $this->getdir());
}

public function clearDir($dir)
{
        if ($h = @opendir($path)) {
            while (FALSE !== ($filename = @readdir($h))) {
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

public function includePhp($filename) {
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