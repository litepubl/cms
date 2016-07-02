<?php
use litepubl\utils\Filer as tfiler;
set_time_limit(120);

function ParseFile($filename) {
global $linescount, $filecount;
//ignore files
if (in_array(basename($filename), array(
'default-skin.css',
'default-skin.inline.css',
'default-skin.inline.less',
'photoswipe.css',
'photoswipe.js',
'photoswipe-ui-default.js',
'Markdown.php',
'MarkdownInterface.php',
'sape.php',
'IXR.php',

))) {
return;
}

$filecount++;
$s = trim(file_get_contents( $filename));
$s = str_replace("\r\n", "\n", $s);
$s = str_replace("\r", "\n", $s);
$s = str_replace('2014', '2016', $s);

    //$s = preg_replace_callback('/\s*\/\*.*?\*\/\s*/sm', function($sc) {
//return preg_replace('/\n{2,}/sm', "\n", $sc[0]);
//}, $s);

if (strend($filename, '.php')) {
$s = replace_copyright($s, 'php');

if (strend($s, '//class')) {
$s = substr($s, 0, strlen($s) - strlen('//class'));
}

//$s = libReplace($s);
//$s = afterFix($s);
//$s = afterFix2($s);

$s = sortUse($s);

if (strend($filename, '.install.php')) {
$s = str_replace('$this', '$self', $s);
}
} else if (strend($filename, '.js')) {
$s = replace_copyright($s, 'js');
} else if (strend($filename, '.less')) {
$s = replace_copyright($s, 'less');
$Lines = explode("\n", $s);
$s = '';
$linescount += count($Lines);
$open = 0;

for ($i=0; $i < count($Lines); $i++) {
$Line = trim($Lines[$i]);
$open = $open - substr_count($Line, '}');
if ($open < 0) {
echo substr($filename, strlen(dirname(dirname(__file__))));
echo "\n$i\n$Line<br>\n";
$s .= $Line. "\n";
} else {
$s .= str_repeat(' ', $open * 2).$Line. "\n";
}

$open = $open + substr_count($Line, "{") ;
}

$s = trim($s);
} else {
$s = replace_copyright($s, 'unknown');
}

$linescount += substr_count($s, "\n");
file_put_contents($filename, $s);
}

function parsedir($dir) {
$ignore = array('class-IXR.php', 'kernel.php', 'engine.php', 'wordpress.functions.php', 'theme.empty.php');
if (strpos($dir, 'geshi')) return;
$list = tfiler::getfiles($dir);
foreach ($list as $filename) {
if (in_array($filename, $ignore)) continue;
if (strbegin($filename, 'kernel.'))continue;
if (strbegin($filename, 'geshi'))continue;
if (strend($filename, '.php')) {
ParseFile($dir . $filename);
} else {
}
}}

function parsejs($dir) {
if (!is_dir($dir)) return;
$list = tfiler::getfiles($dir);
foreach ($list as $filename) {
if (strend($filename, '.min.js')) continue;
if (strend($filename, '.min.css')) continue;
if (strend($filename, '.js') || strend($filename, '.css') || strend($filename, '.less')) {
ParseFile($dir . $filename);
} else {
}
}}

function strbegin($s, $begin) {
  return strncmp($s, $begin, strlen($begin)) == 0;
}

function strend($s, $end) {
  return $end == substr($s, 0 - strlen($end));
}

function BuildKernel($dir){
return;
$iniclasses = parse_ini_file($dir . 'install/ini/classes.ini', true);
$items = $iniclasses['items'];
$inikernel = parse_ini_file($dir . 'install/ini/kernel.ini', true);

foreach ($inikernel as $kernelfile => $kernitems) {
$filelist = array();
foreach ($kernitems as $classname => $kfile) {
$filelist[] = $items[$classname];
}

makekernel($dir, $kernelfile, array_unique($filelist));
}
}

function makekernel($dir, $kernelfilename, array $files) {
$result = "<?php\n";
//$result .= file_get_contents(dirname(__file__) . '\copyright.txt');
foreach ($files as $file) {
//$s = php_strip_whitespace($dir . $file);
$s = trim(file_get_contents($dir . $file));

//strip php tags
$s = substr($s, 5);
if (strend($s, '?>')) $s = substr($s, 0, strlen($s) -2);
$s = trim($s);
if (strbegin($s, '/*')) {
$i = strpos($s, '*/');
$s = substr($s, $i + 2);
$s = trim($s);
}
$result .= "//$file\n";
$result .= $s;
$result .= "\n\n";
}

file_put_contents($dir . $kernelfilename, $result);
}

function parseplugins($rootdir) {
$dir = $rootdir . 'plugins' . DIRECTORY_SEPARATOR;
$list = tfiler::getdir($dir);
foreach ($list as $name) {
parsedir($dir . $name . DIRECTORY_SEPARATOR);
parsejs($dir . $name . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR);
}
}

function replace_copyright($s, $type) {
global $copyright;
if ($type == 'php') {
$s = ltrim(substr($s, 5));
}

if (strbegin($s, '/*')) {
$s = ltrim(substr($s, strpos($s, '*/') + 2));
}

if ($type == 'php') {
$s = "<?php\n" . $copyright . "\n\n" . $s;
} elseif ($type == 'js') {
$s = str_replace('@', ' ', $copyright) . "\n\n" . $s;
} else {
$s = $copyright . "\n\n" . $s;
}

return $s;
}

function parseLibDirs($dir) {
$list = tfiler::getdir($dir);
foreach ($list as $name) {
if ($name == 'include' || $name == 'sape') continue;
parsedir($dir . $name . DIRECTORY_SEPARATOR);
parseLibDirs($dir . $name . DIRECTORY_SEPARATOR);
}
}

$linescount = 0;
$filecount = 0;
$copyright = file_get_contents(dirname(__file__) . '/copyright.txt');
$rootdir = dirname(dirname(dirname(__file__))) . DIRECTORY_SEPARATOR ;
$dir = $rootdir . 'lib' . DIRECTORY_SEPARATOR;
require($rootdir . 'lib/utils/Filer.php');
require (__DIR__ . '/libreplace.php');
$m = microtime(true);

switch (@$_GET['dir']) {
case 'plugins':
parseplugins($rootdir);
break;

case 'js':
$dir = $rootdir . 'js' . DIRECTORY_SEPARATOR . 'litepubl' . DIRECTORY_SEPARATOR;
//foreach (array('admin', 'bootstrap', 'comments', 'common', 'deprecated', 'effects', 'pretty', 'system') as $subdir) {
$subdirs = tfiler::getDir($dir);
foreach ($subdirs as $subdir) {
parsejs($dir . $subdir . DIRECTORY_SEPARATOR);
if (is_dir($dir . $subdir . DIRECTORY_SEPARATOR. 'css')) {
parsejs($dir . $subdir . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR);
}
}

$dir = $rootdir . 'lib' . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR;
parsejs($dir . 'ru' . DIRECTORY_SEPARATOR);
parsejs($dir . 'en' . DIRECTORY_SEPARATOR);
parsejs($rootdir . 'themes' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR);
break;

case 'geo':
//$dir = dirname(dirname(__file__)) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR;
$dir = dirname(dirname(dirname(__file__))) . '/unfuddle/geo/';
foreach (array('geohome', 'geolike', 'geomap', 'geonews', 'geopeople', 'geoplaces', 'geoslider', 'geotheme', 'geotube') as $name) {
parsejs($dir . $name . DIRECTORY_SEPARATOR);
parsedir($dir . $name . DIRECTORY_SEPARATOR);
}
break;

case 'shop':
//$dir = dirname(dirname(__file__)) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR;
$dir = dirname(dirname(dirname(__file__))) . '/unfuddle/shop/';
$list = tfiler::getdir($dir);
foreach ($list as $name) {
if (!strbegin($name, 'shop')) continue;
parsejs("$dir$name/resource/");
parsedir($dir . $name . DIRECTORY_SEPARATOR);
}

$dir .= 'shop/';
$list = tfiler::getdir($dir);
foreach ($list as $name) {
parsejs("$dir$name/resource/");
parsedir($dir . $name . DIRECTORY_SEPARATOR);
}
break;

case 'dir':
$dir = $_GET['dirname'];
parsejs("$dir/resource/");
parsedir($dir);
break;

case 'skip':
return;

default:
ParseFile($rootdir . 'index.php');
parseLibDirs($rootdir . 'lib' . DIRECTORY_SEPARATOR);
//BuildKernel
include ($rootdir . 'lib/install/KernelBuilder.php');
litepubl\install\KernelBuilder::buildAll();
}

echo "<pre>\n";
echo "$linescount = lines count, $filecount = file count\n</pre>\n<pre>";

//echo ord('}');
//echo chr(125);
echo round(microtime(true) - $m, 2), ' = seconds<br>';