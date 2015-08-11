<?php
function update598() {
$t = ttemplate::i();
//remove icons
$t->heads = strtr($t->heads, array(
'<link rel="shortcut icon" type="image/x-icon" href="$site.files/favicon.ico" />' => '',
'<link rel="apple-touch-icon" href="$site.files/apple-touch-icon.png" />' => '',
));

//remove duplicates
$a = explode("\n", $t->heads);
foreach ($a as $s) {
if (($s = trim($s)) && ($first = strpos($t->heads, $s))) {
if (($second = strrpos($t->heads, $s)) && ($first != $second)) {
// remove second string
$t->heads = substr($t->heads, 0, $second) . substr($t->heads, $second + strlen($s) + 1);
}
}
}

$t->heads .= '
<link rel="apple-touch-icon" sizes="57x57" href="$site.files/js/litepubl/logo/apple-touch-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="$site.files/js/litepubl/logo/apple-touch-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="$site.files/js/litepubl/logo/apple-touch-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="$site.files/js/litepubl/logo/apple-touch-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="$site.files/js/litepubl/logo/apple-touch-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="$site.files/js/litepubl/logo/apple-touch-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="$site.files/js/litepubl/logo/apple-touch-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="$site.files/js/litepubl/logo/apple-touch-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="$site.files/apple-touch-icon.png">
<link rel="icon" type="image/png" href="$site.files/js/litepubl/logo/favicon-32x32.png" sizes="32x32">
<link rel="icon" type="image/png" href="$site.files/js/litepubl/logo/android-chrome-192x192.png" sizes="192x192">
<link rel="icon" type="image/png" href="$site.files/js/litepubl/logo/favicon-96x96.png" sizes="96x96">
<link rel="icon" type="image/png" href="$site.files/js/litepubl/logo/favicon-16x16.png" sizes="16x16">
<link rel="manifest" href="$site.files/manifest.json">
<link rel="shortcut icon" href="$site.files/favicon.ico">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="msapplication-TileImage" content="$site.files/js/litepubl/logo/mstile-144x144.png">
<meta name="msapplication-config" content="$site.files/browserconfig.xml">
<meta name="theme-color" content="#ffffff">
';

$t->save();


$parser = tthemeparser::i();
if (!isset($parser->data['tagfiles'])) {
$parser->data['tagfiles'] = array('lib/install/ini/themeparser.ini');
$parser->save();
}

$tc = ttemplatecomments::i();
if (isset($tc->data['logged'])) {
unset($tc->data['logged'], $tc->data['
$tc->save();
}
}