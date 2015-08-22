<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);

define('litepublisher_mode', 'xmlrpc');
include('index.php');
litepublisher::$debug = true;
set_time_limit(300);

$files = tfiles::i();
if ($items = $files->db->getitems("media = 'image' and (width = 0 or height = 0)")) {
echo count($items), ' count<br>';

foreach ($items as $item) {
$srcfilename = litepublisher::$paths->files . $item['filename'];
$info2 = getimagesize($srcfilename);
      $upd = array(
'id' => $item['id'],
'mime' => $info2['mime'],
'width' => $info2[0],
      'height' => $info2[1],
'hash' => $files->gethash($srcfilename),
'size' => filesize($srcfilename),
);

      $files->db->updateassoc($upd);
}
}