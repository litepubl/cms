<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);

function strip_utf($s) {
  $utf = "\xEF\xBB\xBF";
  return strbegin($s, $utf) ? substr($s, strlen($utf)) : $s;
}

function strbegin($s, $begin) {
  return strncmp($s, $begin, strlen($begin)) == 0;
}

$lang = 'ru';
$dir = dirname(__file__);
$s = file_get_contents($dir . '/join.txt');
$s = str_replace('ru/', "$lang/", $s);

$list = explode("\n", $s);

$result = "\xEF\xBB\xBF";
foreach ($list as $filename) {
$filename = trim($filename);
if (!$filename) continue;
$s = file_get_contents("$dir/$filename");
$s =strip_utf($s);
$s = trim($s);

if (!strbegin($filename, 'tml/')) $s = "/* $s */";
$result .= $s . "\r\n\r\n";
}

    $result = str_replace(array("\r\n", "\r"), "\n", $result);
    $result = str_replace("\n", "\r\n", $result);
file_put_contents("$dir/$lang.theme.txt", $result);
echo "theme compiled";