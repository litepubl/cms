<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class taboutparser {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function parse($url) {
    if ($s = http::get($url)) {
      $backuper = tbackuper::i();
      $archtype = $backuper->getarchtype($url);
      if ($files = $backuper->unpack($s, $archtype)) {
        list($filename, $content) = each($files);
        if ($about = self::getabout($files)) {
          $item = new tdownloaditem();
          $item->type = strbegin($filename, 'plugins/') ? 'plugin' : 'theme';
          $item->title = $about['name'];
          $item->downloadurl = $url;
          $item->authorurl = $about['url'];
          $item->authorname = $about['author'];
          $item->rawcontent = $about['description'];
          $item->version = $about['version'];
          $item->tagnames = empty($about['tags']) ? '' : trim($about['tags']);
          if ($screenshot = self::getfile($files, 'screenshot.png')) {
            $media = tmediaparser::i();
            $idscreenshot= $media->uploadthumbnail($about['name'] . '.png', $screenshot);
            $item->files = array($idscreenshot);
          }
          
          return $item;
        }
      }
    }
    return false;
  }
  
  public static function getfile(array &$files, $name) {
    foreach ($files as $filename => &$content) {
      if ($name == basename($filename)) return $content;
    }
    return false;
  }
  
  public static function getabout(array &$files) {
    if ($about_ini = self::getfile($files , 'about.ini')) {
      $about_ini = trim($about_ini );
      //trim unicode sign
      $about_ini = substr($about_ini, strpos($about_ini, '['));
      $about = tini2array::parse($about_ini);
      if (isset($about[litepublisher::$options->language])) {
        $about['about'] = $about[litepublisher::$options->language] + $about['about'];
      }
      return $about['about'];
    }
    return false;
  }
  
}//class