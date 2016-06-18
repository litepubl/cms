<?php

namespace litepubl\updater;

use litepubl\core\Storage;
use litepubl\core\StorageInc;

class StorageIterator
{
    private $callback;
    private $storage;

    public function __construct(Storage $storage, $callback)
    {
        if (!is_callable($callback)) {
            throw new \UnexpectedValueException('No callback');
        }

        $this->callback = $callback;
        $this->storage = $storage;
    }

    public function dir(string $dir)
    {
        $list = dir($dir);
        while ($filename = $list->read()) {
            if ($filename == '.' || $filename == '..' || strpos($filename, '.bak.')) {
                continue;
            }

            if (is_dir($dir . $filename)) {
                $this->dir($dir . $filename . '/');
            } else {
                $this->process($dir . $filename, $to . '/' . $filename);
            }
        }

        $list->close();
    }

    public function process(string $filename)
    {
        $ext = $this->storage->getExt();
        if (substr($filename, 0 - strlen($ext)) == $ext) {
            $basefile = substr($filename, 0, strlen($filename) - strlen($ext));
            $std = new \StdClass();
            if ($std->data = $this->storage->loadData($basefile)) {
                //poolStorage
                if (basename($basefile) == 'storage') {
                    $sub = new \StdClass();
                    foreach ($std->data as $name => $data) {
                        $sub->data = $data;
                        call_user_func_array($this->callback, [$sub]));
                        $std->data[$name] = $sub->data;
                    }

                    $this->storage->saveData($basefile, $std->data);
                } else {
                    if (call_user_func_array($this->callback, [$std])) {
                        $this->storage->saveData($basefile, $std->data);
                    }
                }
            }
        }
    }

    public static function run($callback)
    {
        include_once(dirname(__DIR__) . '/core/AppTrait.php');
        include_once(dirname(__DIR__) . '/core/Storage.php');
        include_once(dirname(__DIR__) . '/core/StorageInc.php');

        $self = new static(
        new StorageInc(),
        $callback);

        $self->dir(dirname(dirname(__DIR__)) . '/storage/data/');
    }
}
