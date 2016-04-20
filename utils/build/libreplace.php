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

$s = renameFunctions($s);
$s = replaceIfReturn($s);
$s = camelCase($s);
return $s;
}

function renameFunctions($s) {
$s = strtr($s, [
'sqldate' => 'sqlDate',
'sqltime' => 'sqlTime',
'dbquote' => 'dbQuote',
'md5rand' => 'md5Rand',
'md5uniq' => 'md5Uniq',
'basemd5' => 'baseMd5',
'strbegin' => 'strBegin',
'strbegins' => 'strBegins',
'strend' => 'strEnd',
'strip_utf' => 'trimUtf',
'array_delete' => 'arrayDelete',
'array_delete_value' => 'arrayDeleteValue',
'array_clean' => 'arrayClean',
'array_insert' => 'arrayInsert',
'array_move' => 'arrayMove',
'strtoarray' => 'strToArray',
'tojson' => 'toJson',
'jsonattr' => 'jsonAttr',
'toenum' => 'toEnum',
]);

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
