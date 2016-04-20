<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

class Storage
 {
use AppTrait;


    public function getExt() {
return '.php';
    }

    public function serialize(array $data) {
        return \serialize($data);
    }

    public function unserialize($str) {
        if ($str) {
            return \unserialize($str);
        }

        return false;
    }

    public function before($str) {
        return \sprintf('<?php /* %s */ ?>', \str_replace('*/', '**//*/', $str));
    }

    public function after($str) {
        return \str_replace('**//*/', '*/', \substr($str, 9, \strlen($str) - 9 - 6));
    }

    public function getFilename(Data $obj) {
        return $this->getApp()->paths->data . $obj->getbasename();
    }

    public function save(Data $obj) {
        return $this->saveFile($this->getfilename($obj) , $this->serialize($obj->data));
    }

    public function saveData($filename, array $data) {
        return $this->saveFile($filename, $this->serialize($data));
    }

    public function load(Data $obj) {
        try {
            if ($data = $this->loadData($this->getfilename($obj))) {
                $obj->data = $data + $obj->data;
                return true;
            }
        }
        catch(\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage();
        }

        return false;
    }

    public function loadData($filename) {
        if ($s = $this->loadFile($filename)) {
            return $this->unserialize($s);
        }

        return false;
    }

    public function loadFile($filename) {
        if (\file_exists($filename . $this->getExt()) && ($s = \file_get_contents($filename . $this->getExt()))) {
            return $this->after($s);
        }

        return false;
    }

    public function saveFile($filename, $content) {
        $tmp = $filename . '.tmp' . $this->getExt();
        if (false === \file_put_contents($tmp, $this->before($content))) {
            $this->error(\sprintf('Error write to file "%s"', $tmp));
            return false;
        }

        \chmod($tmp, 0666);

        //replace file
        $curfile = $filename . $this->getExt();
        if (\file_exists($curfile)) {
            $backfile = $filename . '.bak' . $this->getExt();
            $this->delete($backfile);
            \rename($curfile, $backfile);
        }

        if (!\rename($tmp, $curfile)) {
            $this->error(sprintf('Error rename temp file "%s" to "%s"', $tmp, $curfile));
            return false;
        }

        return true;
    }

    public function remove($filename) {
        $this->delete($filename . $this->getExt());
        $this->delete($filename . '.bak' . $this->getExt());
    }

    public function delete($filename) {
        if (\file_exists($filename) && !\unlink($filename)) {
            \chmod($filename, 0666);
            \unlink($filename);
        }
    }

    public function error($mesg) {
         $this->getApp()->options->trace($mesg);
    }

}