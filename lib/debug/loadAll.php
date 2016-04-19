<?php

namespace litepubl\debug;

function includeDir($dir) {
$list = dir($dir );
while ($name = $list->read()) {
if ($name == '.' || $name == '..') continue;

$filename = $dir .'/' . $name;
if (is_dir($filename)) {
includeDir($filename);
} elseif ('.php' == substr($name, -4)) {
echo "$name<br>";
include_once $filename;
}
}

$list->close();
}

        spl_autoload_register(function($class) {
//echo "$class<br>";
$class = trim($class, '\\');
$class = substr($class, strpos($class, '\\') + 1);
$filename = dirname(__DIR__) . '/' . $class . '.php';
echo "$class<br>";
require $filename;
});
include (__DIR__ . '/kernel.php');
includeDir(dirname(__DIR__));
