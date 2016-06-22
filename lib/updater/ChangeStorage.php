<?php

namespace litepubl\updater;

use litepubl\core\Storage;
use litepubl\core\StorageInc;

class ChangeStorage
{
    private $source;
    private $dest;

    public function __construct(Storage $source, Storage$dest)
    {
        $this->source = $source;
        $this->dest = $dest;
    }

    public function copy(string $from, string $to)
    {
        if (!is_dir($to)) {
            mkdir($to, 0777);
            @chmod($to, 0777);
        }

        $list = dir($from);
        while ($filename = $list->read()) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }

            if (is_dir($from . '/' . $filename)) {
                $this->copy($from . '/' . $filename, $to . '/' . $filename);
            } else {
                $this->convert($from, $to, $filename);
            }
        }

        $list->close();
    }

    public function convert(string $sourcedir, string $destdir, string $filename)
    {
        if (!strpos($filename, '.bak.')) {
$base = basename($filename, $this->source->getExt());
            if ($data = $this->source->loadData($sourcedir . '/' . $base)) {
                $this->dest->saveData($destdir . '/' . $base, $data);
            }
        }
    }

    public static function run(string $dirname)
    {
        include(dirname(__DIR__) . '/core/AppTrait.php');
        include(dirname(__DIR__) . '/core/Storage.php');
        include(dirname(__DIR__) . '/core/StorageInc.php');

        $self = new static(
        new Storage(),
         new StorageInc()
        );

$dir = dirname(dirname(__DIR__)) . '/storage/';
    $temp= 'temp' . time();
    $self->copy($dir . $dirname, $dir . $temp);
    }
}

ChangeStorage::run('data-6.14');
