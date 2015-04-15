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
if (($p->maxwidth == 0) || ($p->maxheight == 0)) die('0 max sizes');
$files = tfiles::i();
if ($items = $files->db->getitems("media = 'image' and parent = 0 and (width > $p->maxwidth or height > $p->maxheight)")) {
echo count($items), ' count<br>';

foreach ($items as $item) {
$srcfilename = litepublisher::$paths->files . $item['filename'];
    if ($source = tmediaparser::readimage($srcfilename)) {
    $sourcex = imagesx($source);
    $sourcey = imagesy($source);
$x = $p->maxwidth;
$y = $p->maxheight;
      $ratio = $sourcex / $sourcey;
      if ($x/$y > $ratio) {
        $x = $y *$ratio;
      } else {
        $y = $x /$ratio;
      }

    $dest = imagecreatetruecolor($x, $y);
    imagecopyresampled($dest, $source, 0, 0, 0, 0, $x, $y, $sourcex, $sourcey);
    imagejpeg($dest, $srcfilename, 95);
    imagedestroy($dest);
@chmod($srcfilename, 0666);
    imagedestroy($source);

//upd

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

$p->getdb('imghashes')->insert(array(
'id' => $item['id'],
'hash' => $item['hash'],
));

echo $item['size'], ' : ', $upd['size'], '<br>';
flush();
}
}
} echo "not found big images<br>";
