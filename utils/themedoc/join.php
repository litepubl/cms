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
$dir = dirname(__file__) . '/';
$dir .= isset($_GET['dir']) ? $_GET['dir'] : 'bootstrap';
$s = file_get_contents($dir . '/join.txt');
$s = trim(str_replace('ru/', "$lang/", $s));
$list = explode("\n", $s);
$l = count($list);

$result = "\xEF\xBB\xBF";

foreach ($list as $i => $filename) {
$filename = trim($filename);
if (!$filename) continue;

$s = file_get_contents("$dir/$filename");
$s =strip_utf($s);
$s = trim($s);
//echo "$filename ", strlen($s), "<br>\n";

if (!strbegin($filename, 'tml/')) {
if (($i == 0) || (($i > 0) && strbegin($list[$i - 1], 'tml/'))) $s = "/*\n" . $s;
if (($i == $l - 1) || (($i < $l - 1) && strbegin($list[$i + 1], 'tml/'))) $s .= "\n*/";
}

$result .= $s;
if ($i < $l - 1) $result .= "\r\n\r\n";
}

    $result = str_replace(array("\r\n", "\r"), "\n", $result);
    $result = str_replace("\n", "\r\n", $result);
//file_put_contents("$dir/theme.txt", $result);
file_put_contents('d:\OpenServer\domains\cms\themes/' 
. (isset($_GET['dir']) ? $_GET['dir'] : 'default') .
'/theme.txt', $result);

//clear theme cache
$cachedir = dirname(dirname(dirname(__file__))) . '/storage/data/themes/';
foreach (glob($cachedir . '*.php') as $filename) {
unlink($filename);
}
echo "theme compiled";