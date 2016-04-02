<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl\plugins;
use litepubl;

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
    $parser->previewwidth = 120;
    $parser->previewheight = 120;
    $parser->previewmode = 'min';
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
    $css->unlock();

    $parser = tmediaparser::i();
    $parser->previewwidth = 120;
    $parser->previewheight = 120;
    $parser->previewmode = 'fixed';
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
    where files.preview > 0 and thumbs.id = files.preview
    "));

    foreach ($items as $i => $item) {
      $srcfilename = litepublisher::$paths->files . $item['filename'];
      $destfilename = litepublisher::$paths->files . $item['filenamethumb'];
      $image = tmediaparser::readimage($srcfilename);
      if ($size = tmediaparser::createthumb($image, $destfilename, $parser->previewwidth, $parser->previewheight, $parser->quality_snapshot, $parser->previewmode)) {
        imagedestroy($image);

        $db->updateassoc(array(
          'id' => $item['idthumb'],
          'width' => $size['width'],
          'height' => $size['height']
        ));
      }
    }

  }

}