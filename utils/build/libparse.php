<?php
use litepubl\utils\Filer as tfiler;
set_time_limit(120);

function ParseFile($filename) {
global $linescount, $filecount, $oBeautify;
//ignore files
if (in_array(basename($filename), array(
'default-skin.css',
'default-skin.inline.css',
'default-skin.inline.less',
'photoswipe.css',
'photoswipe.js',
'photoswipe-ui-default.js',
))) {
return;
}

$filecount++;
$s = trim(file_get_contents( $filename));
$s = str_replace("\r\n", "\n", $s);
$s = str_replace("\r", "\n", $s);
$s = str_replace('2014', '2015', $s);
$s = replace_copyright($s);


    //$s = preg_replace_callback('/\s*\/\*.*?\*\/\s*/sm', function($sc) {
//return preg_replace('/\n{2,}/sm', "\n", $sc[0]);
//}, $s);

if (strend($filename, 'php')) {
$s = replaceIfReturn($s);
        $oBeautify->setInputString($s);
        $oBeautify->process();
$s = $oBeautify->get();
$s = trim($s);
$s .= "\n\n";
} else if (strend($filename, '.less')) {
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
$s .= $Line. "\r\n";
} else {
$s .= str_repeat(' ', $open * 2).$Line. "\n";
}

$open = $open + substr_count($Line, "{") ;
}

$s = trim($s);
}

$linescount += substr_count($s, "\n");
file_put_contents($filename, $s);
}

function replaceIfReturn($str) {
$a = explode("\n", $str);
foreach ($a as $i => $s) {
if (strpos($s, ' if (') && ($j = strpos($s, ' return'))) {
$s = substr($s, 0, $j) . " {\n" . substr($s, $j) . "\n}\n\n";
$a[$i] = $s;
}
}

return implode("\n", $a);
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
if ($name == 'markdown' || $name == 'sape') continue;
parsedir($dir . $name . DIRECTORY_SEPARATOR);
parsejs($dir . $name . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR);
}

ParseFile($dir . 'markdown' . DIRECTORY_SEPARATOR . 'markdown.plugin.class.php');
//sape plugin
ParseFile($dir . 'sape' . DIRECTORY_SEPARATOR . 'sape.plugin.php');
ParseFile($dir . 'sape' . DIRECTORY_SEPARATOR . 'sape.plugin.install.php');
ParseFile($dir . 'sape' . DIRECTORY_SEPARATOR . 'admin.sape.plugin.php');
}

function replace_copyright($s) {
global $copyright;
if ($php = strbegin($s, '<?php')) {
$s = ltrim(substr($s, 5));
}

if (strbegin($s, '/*')) {
$s = ltrim(substr($s, strpos($s, '*/') + 2));
}


/*
//if ($php && ! strbegin($s, 'namespace')) {
if ($php) {
$s = str_replace(
"namespace litepubl\plugins;\nuse litepubl;\n\n",
"namespace litepubl;\n\n",
$s);
}
*/
//if($php) $s = str_replace('litepublisher::', 'litepubl::', $s);
//if($php) $s = str_replace('self::', 'static::', $s);
if($php) {
$s = strtr([
' litepubl::$' => ' \litepubl::$',
'(litepubl::$' => '(\litepubl::$',
'litepubl::$urlmap' => 'litepubl::$router',
'turlmap::unsub', 'litepubl::$router->unbind',
'tlocal', 'Lang',
'new targs' => 'new Args',
'targs::i()' => 'new Args()',
'tadminhtml::array2combo' => '$this->theme->comboItems',
'$form->items ' => '$form->body ',
'tablebuilder' => 'Table',

]);
}

$s = ($php ? "<?php\n" : '') . $copyright . "\n\n" . $s;
return $s;
}

$linescount = 0;
$filecount = 0;
$copyright = file_get_contents(dirname(__file__) . '/copyright.txt');
$rootdir = dirname(dirname(dirname(__file__))) . DIRECTORY_SEPARATOR ;
$dir = $rootdir . 'lib' . DIRECTORY_SEPARATOR;
require($dir . 'filer.class.php');
$m = microtime(true);
require (dirname(__file__) . '/PHP_Beautifier/Beautifier.php');
        $oBeautify = new PHP_Beautifier();
$oBeautify->setIndentNumber(4);
$oBeautify->setNewLine("\n");
        $oBeautify->addFilter('ArrayNested');
        $oBeautify->addFilter('Pear',array(
'add_header'=>'php',
'newline_class'=>true,
 'newline_function'=>true,
));
        $oBeautify->addFilter('KeepEmptyLines');
//echo round(microtime(true) - $m, 2), ' = load beauty<br>';

switch (@$_GET['dir']) {
case 'plugins':
parseplugins($rootdir);
break;

case 'js':
$dir = $rootdir . 'js' . DIRECTORY_SEPARATOR . 'litepubl' . DIRECTORY_SEPARATOR;
foreach (array('admin', 'bootstrap', 'comments', 'common', 'deprecated', 'effects', 'pretty', 'system') as $subdir) {
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
parsedir($rootdir . 'lib' . DIRECTORY_SEPARATOR);
parsedir($rootdir . 'lib' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR);
BuildKernel($rootdir . 'lib' . DIRECTORY_SEPARATOR);
}

echo "<pre>\n";
echo "$linescount = lines count, $filecount = file count\n</pre>\n<pre>";

//echo ord('}');
//echo chr(125);
echo round(microtime(true) - $m, 2), ' = seconds<br>';