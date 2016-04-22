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
public $dir;

public function __construct($dir) {
$this->dir = $dir;
$this->items = [];
}

    public function getDir() {
        return  $this->dir;
    }

    public function setString($filename, $str) {
$this->items[$name] = $str;
        $fn = $this->getdir() . $filename;
        file_put_contents($fn, $str);
        @chmod($fn, 0666);
    }

    public function getString($filename) {
if (array_key_exists($filename, $this->items)) {
return $this->items[$filename];
}

        $fn = $this->getdir() . $filename;
        if (file_exists($fn)) {
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
        return array_key_exists($filename, $this->items) || file_exists($this->getdir() . $filename);
    }

    public function clear() {
$this->items = [];
        $path = $this->getdir();
        if ($h = @opendir($path)) {
            while (FALSE !== ($filename = @readdir($h))) {
                if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) {
 continue;
}


                $file = $path . $filename;
                if (is_dir($file)) {
                    tfiler::delete($file . DIRECTORY_SEPARATOR, true, true);
                } else {
                    unlink($file);
                }
            }
            closedir($h);
        }
    }

public function includePhp($filename) {
if (file_exists($this->getDir() . $filename)) {
include ($this->getDir() . $filename);
}
}

}