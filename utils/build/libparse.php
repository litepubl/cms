<?php
//php_strip_whitespace

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
))) {
return;
}

//if (strend($filename, '.js')) echo basename($filename) . "\n";
$filecount++;
//echo substr($filename, strlen(dirname(dirname(__file__)))), "\n";
$s = trim(file_get_contents( $filename));
$s = str_replace('2014', '2015', $s);
$s = replace_copyright($s);
$Lines = explode("\n", $s);
$linescount += count($Lines);

$open = 0;
$Result = "";
for ($i=0; $i < count($Lines); $i++) {
$Line = trim($Lines[$i]);
$open = $open - substr_count($Line, '}');
if ($open < 0) {
echo substr($filename, strlen(dirname(dirname(__file__))));
echo "\n$i\n$Line<br>\n";
$Result .= $Line. "\r\n";
} else {
$Result .= str_repeat(' ', $open * 2).$Line. "\r\n";
}
$open = $open + substr_count($Line, "{") ;
}
//$s = implode("\n", $Lines);
$Result = trim($Result);
file_put_contents($filename, $Result);
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
makekernel($dir, 'kernel.php', array(
'db.class.php',
'data.class.php',
'array2prop.class.php',
'utils.functions.php',

 'events.class.php',
'events.exception.class.php',
'events.coclass.php',
'events.storage.class.php',

 'items.class.php',
'items.storage.class.php',
'items.single.class.php',
'item.class.php',
'item.storage.class.php',
 'classes.class.php',
'classes.functions.php',
 'options.class.php',
 'site.class.php',
 'urlmap.class.php',
 'interfaces.php',
 'plugin.class.php',
'users.class.php',
//'users.groups.class.php'
'items.pool.class.php',
'storage.file.class.php',
'storage.class.php',
'storage.mem.class.php',
'storage.cache.file.class.php',
'storage.cache.memcache.class.php',
));

makekernel($dir, 'kernel.templates.php',  array(
'local.class.php',
'inifiles.class.php',
'view.class.php', 
'views.class.php', 
'events.itemplate.class.php',
'items.itemplate.class.php',
'template.class.php',
'theme.base.class.php',
'theme.class.php',
'theme.args.class.php',
'theme.vars.class.php',
'widget.class.php',
'widget.order.class.php',
'widget.class.class.php',
'widgets.class.php',
'widgets.cache.class.php',
'guard.class.php'
));

makekernel($dir, 'kernel.posts.php',  array(
'items.posts.class.php',
'post.class.php',
'post.factory.class.php',
'posts.class.php',
'post.transform.class.php',
'post.meta.class.php',
'widget.posts.class.php',
'tags.common.class.php',
'tags.factory.class.php',
'tags.categories.class.php',
'tags.class.php',
'widget.commontags.class.php',
'widget.categories.class.php',
'widget.tags.class.php',
'files.class.php',
'files.items.class.php',
));

makekernel($dir, 'kernel.comments.class.db.php',  array(
'comments.class.db.php',
'comments.manager.class.php',
'comments.form.class.php',
'comments.subscribers.class.php',
'template.comments.class.php',
'widget.comments.class.php'
));

/*
makekernel($dir, 'kernel.comments.class.files.php',  array(
'comments.class.files.php',
'comments.manager.class.files.php',
'comments.form.class.files.php',
'comments.spamfilter.class.php',
'comments.subscribers.class.php',
'comments.users.class.files.php',
'template.comments.class.php',
'widget.comments.class.php'
));
*/

makekernel($dir, 'kernel.admin.php',  array(
'menus.admin.class.php',
'menu.admin.class.php',
'author-rights.class.php',
'theme.admin.class.php',
'htmlresource.class.php',
'html.adminform.class.php',
'html.uitabs.class.php',
'html.tag.class.php',
'html.autoform.class.php',
'html.tablebuilder.class.php',
'filter.datetime.class.php',
'admin.posteditor.ajax.class.php',
'admin.posteditor.class.php',
));

}

function makekernel($dir, $kernelfilename, array $files) {
$result = "<?php\n";
//$result .= file_get_contents(dirname(__file__) . '\copyright.txt');
foreach ($files as $file) {
//$s = php_strip_whitespace($dir . $file);
$s = trim(file_get_contents($dir . $file));

//обрезать теги php 
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
//js poll
ParseFile($dir . 'polls' . DIRECTORY_SEPARATOR . 'polls.client.js');
}

function replace_copyright($s) {
global $copyright;
if ($php = strbegin($s, '<?php')) {
$s = ltrim(substr($s, 5));
}

if (strbegin($s, '/*')) {
$s = ltrim(substr($s, strpos($s, '*/') + 2));
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