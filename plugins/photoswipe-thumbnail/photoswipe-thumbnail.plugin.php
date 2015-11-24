<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class photoswipethumbnail extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function install() {
    $plugindir = basename(dirname(__file__));
    $js = tjsmerger::i();
    $js->lock();
    $js->add('default', "plugins/$plugindir/resource/thumbnails.min.js");
    $js->unlock();
    
    $css = tcssmerger::i();
    $css->lock();
    $css->add('default', "plugins/$plugindir/resource/thumbnails.min.css");
    $css->unlock();

$parser = tmediaparser::i();
$parser->previewwidth = 0;
$parser->previewheight = 150;
$parser->ratio = true;
$parser->clipbounds = false;
$parser->save();

$files = tfiles::i();
$db = $files->db;
$thumbs = $db->res2items($db->query("
(select parent from $db->files where height > 0 and height <> $parser->previewheight)
");

$items = $files->db->getitems("id in
(select parent from $db->files where height > 0 and height <> $parser->previewheight)
");

foreach ($items as $item) {
$srcfilename = litepublisher::$paths->files . $item['filename'];
$destfilename = litepublisher::$paths->files . $item['destfilename'];
$source = tmediaparser::readimage($srcfilename);

if ($size = tmediaparser::createthumb($image, $destfilename, $parser->previewwidth, $parser->previewheight, $parser->ratio, $parser->clipbounds, $parser->quality_snapshot)) {
$db->updateassoc(array(
'id' => $item['idpre'],
'width' => $size['width'],
'height' => $size['height']
));
}
}

  }
  
  public function uninstall() {
    $plugindir = basename(dirname(__file__));
    $js = tjsmerger::i();
    $js->lock();
    $js->deletefile('default', "plugins/$plugindir/resource/thumbnails.min.js");
    $js->unlock();
    
    $css = tcssmerger::i();
    $css->lock();
    $css->deletefile('default', "plugins/$plugindir/resource/thumbnails.min.css");
    $css->deletefile('default', "plugins/$plugindir/resource/default-skin/default-skin.inline.min.css");
    $css->unlock();
  }
  
}//class