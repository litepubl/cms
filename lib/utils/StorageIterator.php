<?php

namespace litepubl\utils;

use litepubl\core\Storage;
use litepubl\core\StorageInc;

class StorageIterator 
{
private $callback;
public $storage;

public function __construct($callback)
{
if (!is_callable($callback)) {
throw new \UnexpectedValueException('No callback');
}

$this->callback = $callback;
}

public function dir(string $dir)
{
$list = dir($dir);
while($filename = $list->read()) {
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
$filename = substr($filename, 0, strlen($filename) - strlen($ext));
$std = new \StdClass();
if ($std->data = $this->storage->loadData($filename)) {
if (call_user_func_array($this->callback, [$std])) {
$this->storage->saveData($filename, $std->data);
}
}
}
}

public static function run($callback)
{
include (dirname(__DIR__) . '/core/AppTrait.php');
include (dirname(__DIR__) . '/core/Storage.php');
include (dirname(__DIR__) . '/core/StorageInc.php');

$self = new static($callback);
$self->storage = new StorageInc();
$self->dir(dirname(dirname(__DIR__)) . '/storage/data/');
}
}


StorageIterator::run(function(\StdClass $std) {
if (isset($std->data['events']) && count($std->data['events'])) {
foreach ($std->data['events'] as $name => $events) {
unset($std->data['events'][$name]);
$name = strtolower($name);
$std->data['events'][$name] = $events;
echo "$name\n";
}

return true;
}
});