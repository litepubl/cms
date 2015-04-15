<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
$o = litepublisher::$options;
echo "<pre>\n";
echo "current options
$o->filetime_offset = file timeoffset
$o->timezone = time zone
";

$f = litepublisher::$paths->data . 'index.htm';
echo date('r', filemtime($f)), " = file time\n";
touch($f);
clearstatcache();
echo date('r', filemtime($f)), " = file time\n";
echo tfiler::get_filetime_offset(), " = time offset tfiler\n";
