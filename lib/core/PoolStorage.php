<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\core;

class PoolStorage
{
    use AppTrait;

    public $data;
    private $modified;

    public function __construct()
    {
        $this->data = [];
        $this->loadData();
    }

    public function getStorage()
    {
        return $this->getApp()->storage;
    }

    public function save(Data $obj)
    {
        $this->modified = true;
        $base = $obj->getBaseName();
        if (!isset($this->data[$base])) {
            $this->data[$base] = & $obj->data;
        }

        return true;
    }

    public function load(Data $obj)
    {
        $base = $obj->getbasename();
        if (isset($this->data[$base])) {
            $obj->data = & $this->data[$base];
            return true;
        } else {
            $this->data[$base] = & $obj->data;
            return false;
        }
    }

    public function remove(Data $obj)
    {
        $base = $obj->getBaseName();
        if (isset($this->data[$base])) {
            unset($this->data[$base]);
            $this->modified = true;
            return true;
        }
    }

    public function loadData()
    {
        if ($data = $this->getStorage()->loaddata($this->getApp()->paths->data . 'storage')) {
            $this->data = $data;
            return true;
        }

        return false;
    }

    public function commit()
    {
        if (!$this->modified) {
            return false;
        }

        $lockfile = $this->getApp()->paths->data . 'storage.lok';
        if (($fh = @\fopen($lockfile, 'w')) && \flock($fh, LOCK_EX | LOCK_NB)) {
            $this->getStorage()->saveData($this->getApp()->paths->data . 'storage', $this->data);
            $this->modified = false;
            \flock($fh, LOCK_UN);
            \fclose($fh);
            @\chmod($lockfile, 0666);
            return true;
        } else {
            if ($fh) {
                @\fclose($fh);
            }

            $this->error('Storage locked, data not saved');
            return false;
        }
    }

    public function error($mesg)
    {
        $this->getApp()->getLogger()->error($mesg);
    }

    public function isInstalled()
    {
        return count($this->data);
    }
}
