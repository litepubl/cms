<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\debug;

function includeDir($dir)
{
    $list = dir($dir);
    while ($name = $list->read()) {
        if ($name == '.' || $name == '..' || $name == 'kernel.php') {
            continue;
        }

        $filename = $dir . '/' . $name;
        if (is_dir($filename)) {
            if ($name != 'include') {
                includeDir($filename);
            }
        } elseif ('.php' == substr($name, -4)) {
            echo "$name<br>";
            include_once $filename;
        }
    }

    $list->close();
}

spl_autoload_register(function ($class) {

    //echo "$class<br>";
    $class = trim($class, '\\');
    $class = substr($class, strpos($class, '\\') + 1);
    $filename = dirname(__DIR__) . '/' . $class . '.php';
    echo "$class<br>";
    //echo "$filename\n";
    require $filename;
});

//include (dirname(dirname(__DIR__ )). '/index.debug.php');
include(__DIR__ . '/Config.php');
include(__DIR__ . '/kernel.php');

includeDir(dirname(__DIR__));
includeDir(dirname(dirname(__DIR__)) . '/plugins');
