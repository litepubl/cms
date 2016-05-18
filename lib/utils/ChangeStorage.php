<?php

namespace litepubl\utils;

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

public function switch(string $dir)
{
$dir = rtrim($dir, '/\\');
$temp= dirname($dir) . '/temp' . time();
$this->copy($dir, $temp);
//rename($dir, $dir . '-old');
//rename($temp, $dir);
}

public function copy(string $from, string $to)
{
if (!is_dir($to)) {
mkdir($to, 0777);
@chmod($to, 0777);
}

$list = dir($from);
while($filename = $list->read()) {
if ($filename == '.' || $filename == '..') {
continue;
}

if (is_dir($from . '/' . $filename)) {
$this->copy($from . '/' . $filename, $to . '/' . $filename);
} else {
$this->convert($from . '/' . $filename, $to . '/' . $filename);
}
}

$list->close();
}

public function convert($sourcefile, $destfile)
{
if (!strpos($sourcefile, '.bak.')) {
if ($data = $this->source->loadData(basename($sourcefile, $this->source->getExt()))) {
$this->dest->saveData(basename($destfile, $this->source->getExt()), $data);
}
}
}

public static function run()
{
include (dirname(__DIR__) . '/core/AppTrait.php');
include (dirname(__DIR__) . '/core/Storage.php');
include (dirname(__DIR__) . '/core/StorageInc.php');

$self = new static(new Storage(), new StorageInc());
$self->switch(dirname(dirname(__DIR__)) . '/storage/data');
}
}

ChangeStorage::run();