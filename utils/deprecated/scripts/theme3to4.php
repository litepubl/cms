<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');

function auto_convert_themes() {
$dir = litepublisher::$paths->home . 'themes4' . DIRECTORY_SEPARATOR;
@mkdir($dir, 0777);
@chmod($dir, 0777);
    $list =    tfiler::getdir(litepublisher::$paths->themes);
    sort($list);
echo "<pre>\n";
foreach ($list as $name) {
echo "$name theme:\n";
if ($name == 'default') continue;
$newdir = $dir . $name;
@mkdir($newdir, 0777);
@chmod($newdir, 0777);
$theme = ttheme::getinstance($name);
tthemeparser::compress($theme, $newdir . DIRECTORY_SEPARATOR);
}

}

auto_convert_themes();