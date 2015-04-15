<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);

define('litepublisher_mode', 'xmlrpc');
include('index.php');
litepublisher::$debug = true;
set_time_limit(300);

$p = tmediaparser::i();
$files = tfiles::i();
$items = $files->db->getitems("media = 'image' and parent = 0");
echo count($items), ' count<br>';
foreach ($items as $item) {
$srcfilename = litepublisher::$paths->files . $item['filename'];
echo "$srcfilename <br>\n";
    if ($source = tmediaparser::readimage($srcfilename)) {
$p->getsnapshot($srcfilename, $source);
echo "rescale<br>\n";
} else {
echo "error<br>\n";
}
}

echo "finished<br>\n";