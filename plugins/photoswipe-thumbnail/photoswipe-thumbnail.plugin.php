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
    $css->add('admin', "plugins/$plugindir/resource/admin.thumbnails.min.css");
    $css->unlock();
    
    $parser = tmediaparser::i();
    $parser->previewwidth = 0;
    $parser->previewheight = 150;
    $parser->ratio = true;
    $parser->clipbounds = false;
    $parser->save();
    
    $this->rescale();
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
    $css->deletefile('admin', "plugins/$plugindir/resource/admin.thumbnails.min.css");
    $css->unlock();
    
    $parser = tmediaparser::i();
    $parser->previewwidth = 150;
    $parser->previewheight = 150;
    $parser->ratio = true;
    $parser->clipbounds = true;
    $parser->save();
    
    $this->rescale();
  }
  
  public function rescale() {
    $parser = tmediaparser::i();
    $files = tfiles::i();
    $db = $files->db;
    $t = $files->thistable;
    
    $items = $db->res2assoc($db->query("
    select  files.id as id, files.filename as filename, thumbs.id as idthumb, thumbs.filename as filenamethumb
    from $t  files, $t thumbs
    where thumbs.parent > 0 and thumbs.height <> $parser->previewheight and files.id = thumbs.parent
    "));
    
    foreach ($items as $item) {
      $srcfilename = litepublisher::$paths->files . $item['filename'];
      $destfilename = litepublisher::$paths->files . $item['filenamethumb'];
      $image = tmediaparser::readimage($srcfilename);
      if ($size = tmediaparser::createthumb($image, $destfilename, $parser->previewwidth, $parser->previewheight, $parser->ratio, $parser->clipbounds, $parser->quality_snapshot)) {
        $db->updateassoc(array(
        'id' => $item['idthumb'],
        'width' => $size['width'],
        'height' => $size['height']
        ));
      }
    }
    
  }
  
}