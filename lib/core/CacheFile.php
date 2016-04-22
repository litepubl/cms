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
}

    public function getDir() {
        return  $this->dir;
    }

    public function setString($filename, $str) {
        $fn = $this->getdir() . $filename;
        file_put_contents($fn, $str);
        @chmod($fn, 0666);
    }

    public function getString($filename) {
        $fn = $this->getdir() . $filename;
        if (file_exists($fn)) {
            return file_get_contents($fn);
        }

        return false;
    }

    public function delete($filename) {
        $fn = $this->getdir() . $filename;
        if (file_exists($fn)) {
            unlink($fn);
        }
    }

    public function exists($filename) {
        return file_exists($this->getdir() . $filename);
    }

    public function clear() {
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

}