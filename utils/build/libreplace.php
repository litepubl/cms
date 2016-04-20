<?php

function libReplace($s) {
$s = strtr([
'litepubl::$secret' => 'Config::$secret',
'litepubl::$debug' => 'Config::$debug',
'config::$' => 'config::$',
'$urlmap' => '$router',
'idview' => 'idschema';
'tviews::' =>'Schemas::';
'tview::' => 'Schema::',
'$views' => '$schemes',
'$view' => '$schema',
'litepubl::$app' => 'litepubl@app',
'turlmap::unsub', 'litepubl::$router->unbind',
]);

$s = strtr([
'litepubl::$' => ' $this->getApp()->',
'litepubl@app' => 'litepubl::$app',
'tlocal', 'Lang',
'new targs' => 'new Args',
'targs::i()' => 'new Args()',
'tadminhtml::array2combo' => '$this->theme->comboItems',
'$form->items ' => '$form->body ',
'tablebuilder' => 'Table',
' Ulist' => ' UList',
'processform' => 'processForm',
'doprocessform' => 'doProcessForm',
'DataStorage' => 'SharedStorage',
]);

$s = insertUse($s, 'Config::', 'litepubl\Config');
$s = renameFunctions($s);
$s = replaceIfReturn($s);
$s = camelCase($s);
return $s;
}

function renameFunctions($s) {
$s = strtr($s, [
'sqldate' => 'Str::sqlDate',
'sqltime' => 'Str::sqlTime',
'dbquote' => 'Str::uuote',
'md5rand' => 'Str::md5Rand',
'md5uniq' => 'Str::md5Uniq',
'basemd5' => 'Str::baseMd5',
'strbegin' => 'Str::begin',
'strbegins' => 'Str::begins',
'strend' => 'Str::end',
'strip_utf' => 'Str::trimUtf',
'array_delete' => 'Arr::delete',
'array_delete_value' => 'Arr::deleteValue',
'array_clean' => 'Arr::clean',
'array_insert' => 'Arr::insert',
'array_move' => 'Arr::move',
'strtoarray' => 'Str::toArray',
'tojson' => 'Str::toJson',
'jsonattr' => 'Str::jsonAttr',
'toenum' => 'Arr::toEnum',
'tdatabase::str2array' => 'Str::toIntArray',
'dumpstr' => 'Str::dump',
'dumpvar' => 'Str::log',
]);

if (false === strpos($s, 'namespace litepubl\core')) {
$s = insertUse($s, 'Str::', 'litepubl\core\Str');
$s = insertUse($s, 'Arr::', 'litepubl\core\Arr');
}

return $s;
}

function insertUse($s, $fnc, $ns) {
if (!strpos($s, $fnc)) return $s;
$uns = "use $ns;";
if (strpos($s, $uns)) return;

$i = strpos($s, "\n\n", strpos($s, 'namespace '));
if (!$i) {
echo "Cant insert $uns<br>";
return$s;
}

$s = substr($s, 0, $i) . "\n" . $uns . substr($s, $i);
return $s;
}

function replaceIfReturn($str) {
$a = explode("\n", $str);
foreach ($a as $i => $s) {
if (strpos($s, ' if (') && 
(($j = strpos($s, ' return'))) || 
($j = strpos($s, ' continue'))) {
$s = substr($s, 0, $j) . " {\n" . substr($s, $j) . "\n}\n\n";
$a[$i] = $s;
}
}

return implode("\n", $a);
}

function camelCase($s) {
$a = explode("\n", $s);
$func = ' function ';
foreach ($a as $i => $s) {
if ($j = strpos($s, $func)) {
$s2 = ltrim(substr($s, $j + strlen($func)));
if (strbegin($s2, 'get') || strbegin($s2, 'set')) {
$s2[3] = strtoupper($s2[3]);
$a[$i] = substr($s, 0, $j) . $func . $s2;
}
}
}

return implode("\n", $a);
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
