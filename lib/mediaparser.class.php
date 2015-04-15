<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmediaparser extends tevents {
  
  public   static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'mediaparser';
    $this->addevents('added', 'onbefore', 'onresize', 'noresize', 'onimage');
    $this->data['enablepreview'] = true;
    $this->data['ratio'] = true;
    $this->data['clipbounds'] = true;
    $this->data['previewwidth'] = 120;
    $this->data['previewheight'] = 120;
    $this->data['maxwidth'] = 1200;
    $this->data['maxheight'] = 900;
    $this->data['quality_snapshot'] = 94;
    $this->data['quality_original'] = 92;
    $this->data['alwaysresize'] = false;
    
    $this->data['audioext'] = 'mp3|wav |flac';
    $this->data['videoext'] = 'mp4|ogv|webm';
  }
  
  public static function fixfilename($filename) {
    if (preg_match('/\.(htm|html|js|php|phtml|php\d|htaccess)$/i', $filename)) return $filename . '.txt';
    return $filename;
  }
  
  public static function linkgen($filename) {
    $filename = tlinkgenerator::i()->filterfilename($filename);
    return self::fixfilename($filename);
  }
  
  public function addlocal($filename) {
    return $this->upload(basename($filename), file_get_contents($filename), '', '', '', false);
  }
  
  public function upload($filename, $content, $title, $description, $keywords, $overwrite ) {
    if ($title == '') $title = $filename;
    $filename = self::linkgen($filename);
    $tempfilename = $this->doupload($filename, $content);
    return $this->addfile($filename, $tempfilename, $title, $description, $keywords, $overwrite);
  }
  
  public function gettempname($parts) {
    return 'tmp.' . md5rand() . '.' . $parts['filename'] .
    (empty($parts['extension']) ? '' : '.' . $parts['extension']);
  }
  
  public function uploadfile($filename, $tempfilename, $title, $description, $keywords, $overwrite ) {
    if ($title == '') $title = $filename;
    if ($description == '') $description = $title;
    $filename = self::linkgen($filename);
    $parts = pathinfo($filename);
    $newtemp = $this->gettempname($parts);
    if (!move_uploaded_file($tempfilename, litepublisher::$paths->files . $newtemp)) return $this->error('Error access to uploaded file');
    //return $this->addfile($filename, $newtemp, $title, $description, $keywords, $overwrite);
    return $this->add(array(
    'filename' => $filename,
    'tempfilename' => $newtemp,
    'title' => $title,
    'description' => $description,
    'keywords' => $keywords,
    'overwrite' => $overwrite
    ));
  }
  
  public static function move_uploaded($filename, $tempfilename, $subdir) {
    $filename = self::linkgen($filename);
    $filename = self::create_filename($filename, $subdir, false);
    $sep = $subdir == '' ? '' : $subdir . DIRECTORY_SEPARATOR;
    if (!move_uploaded_file($tempfilename, litepublisher::$paths->files . $sep . $filename)) return false;
    return $subdir == '' ? $filename : "$subdir/$filename";
  }
  
  public static function prepare_filename($filename, $subdir) {
    $filename = self::linkgen($filename);
    $filename = self::create_filename($filename, $subdir, false);
    return $subdir == '' ? $filename : "$subdir/$filename";
  }
  
  public function uploadicon($filename, $content, $overwrite ) {
    $filename = self::linkgen($filename);    $tempfilename = $this->doupload($filename, $content, $overwrite);
    $info = $this->getinfo($tempfilename);
    if ($info['media'] != 'image') $this->error('Invalid icon file format '. $info['media']);
    $info['media'] = 'icon';
    $info['filename'] = $this->movetofolder($filename, $tempfilename, 'icon', $overwrite);
    $item = $info + array(
    'title' => '',
    'description' => ''
    );
    
    $files = tfiles::i();
    return $files->additem($item);
  }
  
  public function addicon($filename) {
    $info = $this->getinfo($filename);
    if ($info['media'] != 'image') $this->error('Invalid icon file format '. $info['media']);
    $info['media'] = 'icon';
    $item = $info + array(
    'filename' => $filename,
    'title' => '',
    'description' => ''
    );
    
    $files = tfiles::i();
    return $files->additem($item);
  }
  
  private function doupload($filename, &$content) {
    $filename = self::fixfilename($filename);
    $parts = pathinfo($filename);
    $filename = $this->gettempname($parts);
    if (@file_put_contents(litepublisher::$paths->files . $filename, $content)) {
      @ chmod(litepublisher::$paths->files. $filename, 0666);
      return $filename;
    }
    return false;
  }
  
  public static function replace_ext($filename, $ext) {
    $parts = pathinfo($filename);
    $result = $parts['filename'] . $ext;
    if (!empty($parts['dirname']) && ($parts['dirname'] != '.')) $result = $parts['dirname'] . DIRECTORY_SEPARATOR . $result;
    return $result;
  }
  
  public static function makeunique($filename) {
    $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
    $i = strrpos($filename, DIRECTORY_SEPARATOR);
    $dir = substr($filename, 0, $i +1);
    return $dir . self::getunique($dir, substr($filename, $i + 1));
  }
  
  public static function getunique($dir, $filename) {
    $files = tfiles::i();
    $subdir = basename(rtrim($dir, '/' .DIRECTORY_SEPARATOR)) . '/';
    if  (!$files->exists($subdir . $filename) && !@file_exists($dir . $filename)) return $filename;
    $parts = pathinfo($filename);
    $base = $parts['filename'];
    $ext = empty($parts['extension']) ? '' : ".$parts[extension]";
    for ($i = 2; $i < 10000; $i++) {
      $filename = "$base$i$ext";
      if  (!$files->exists($subdir . $filename) && !file_exists($dir . $filename)) return $filename;
    }
    return $filename;
  }
  
  public static function create_filename($filename, $subdir, $overwrite) {
    $dir = litepublisher::$paths->files . $subdir;
    if (!is_dir($dir)) {
      mkdir($dir, 0777);
      @chmod($dir, 0777);
    }
    if ($subdir) $dir .= DIRECTORY_SEPARATOR;
    if ($overwrite  )  {
      if (file_exists($dir . $filename)) unlink($dir . $filename);
    } else {
      $filename = self::getunique($dir, $filename);
    }
    
    return $filename;
  }
  
  public function getmediafolder($media) {
    if (isset($this->data[$media])) {
      if ($result = $this->data[$media]) return $result;
    }
    return $media;
  }
  
  public function movetofolder($filename, $tempfilename, $subdir, $overwrite) {
    $filename = self::create_filename($filename, $subdir, $overwrite);
    $sep = $subdir == '' ? '' : $subdir . DIRECTORY_SEPARATOR;
    if (!rename(litepublisher::$paths->files . $tempfilename, litepublisher::$paths->files . $sep . $filename)) return $this->error(sprintf('Error rename file %s to %s',$tempfilename, $filename));
    return $subdir == '' ? $filename : "$subdir/$filename";
  }
  
  public function addfile($filename, $tempfilename, $title, $description, $keywords, $overwrite) {
    return $this->add(array(
    'filename' => $filename,
    'tempfilename' => $tempfilename,
    'title' => $title,
    'description' => $description,
    'keywords' => $keywords,
    'overwrite' => $overwrite
    ));
  }
  
  public function add(array $file) {
    if (!isset($file['filename']) || !isset($file['tempfilename'])) $this->error('No file name');
    $files = tfiles::i();
    $hash =$files->gethash(litepublisher::$paths->files . $file['tempfilename']);
    if (($id = $files->indexof('hash', $hash)) ||
    ($id = $files->getdb('imghashes')->findid('hash = '. dbquote($hash)))) {
      @unlink(litepublisher::$paths->files . $file['tempfilename']);
      return $id;
    }
    
    $item = $this->getinfo($file['tempfilename']);
    $item = array_merge($item, array(
    'filename' => $this->movetofolder($file['filename'], $file['tempfilename'], $this->getmediafolder($item['media']), isset($file['overwrite']) ? $file['overwrite'] : false),
    'title' => isset($file['title']) ? $file['title']: $filename,
    'description' => isset($file['description']) ? $file['description'] : '',
    'keywords' => isset($file['keywords']) ? $file['keywords'] : ''
    ));
    
    $preview = false;
    if ($item['media'] == 'image') {
      $srcfilename = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
      $this->callevent('onbefore', array(&$item, $srcfilename));
      $maxwidth = isset($file['maxwidth']) ? $file['maxwidth'] : $this->maxwidth;
      $maxheight = isset($file['maxheight']) ? $file['maxheight'] : $this->maxheight;
      $resize = $this->alwaysresize && ($maxwidth > 0) && ($maxheight > 0);
      if (!$resize) $resize = ($item['width'] > $maxwidth ) || ($item['height'] > $maxheight);
      $enablepreview = isset($file['enablepreview']) ? $file['enablepreview'] : (isset($file['ispreview']) ? $file['ispreview'] : $this->enablepreview);
      if (($resize || $enablepreview) && ($image = self::readimage($srcfilename))) {
        $this->onimage($image);
        if ($enablepreview && ($preview = $this->getsnapshot($srcfilename, $image))) {
          $preview['title'] = $file['title'];
          if (isset($file['ispreview']) && $file['ispreview']) {
            $item['filename'] = $preview['filename'];
            $item['width'] = $preview['width'];
            $item['height'] = $preview['height'];
            $item['mime'] = $preview['mime'];
            @unlink($srcfilename);
            $resize = false;
            $preview = false;
          }
        }
        
        if ($resize) {
          $sizes = $this->resize($srcfilename, $image, $maxwidth, $maxheight);
          $item['width'] = $sizes['width'];
          $item['height'] = $sizes['height'];
          // after resize only jpg format
          if (!strend($srcfilename, '.jpg')) {
            $fixfilename = self::replace_ext($srcfilename, '.jpg');
            $fixfilename = self::makeunique($fixfilename);
            rename($srcfilename, $fixfilename);
            @chmod($fixfilename, 0666);
            $item['filename'] = str_replace(DIRECTORY_SEPARATOR, '/', substr($fixfilename, strlen(litepublisher::$paths->files)));
          }
        } else {
          $this->noresize($image, $srcfilename);
        }
        
        imagedestroy($image);
      }
    }
    
    $id = $files->additem($item);
    IF ($hash != $files->getvalue($id, 'hash')) {
      $files->getdb('imghashes')->insert(array(
      'id' => $id,
      'hash' => $hash
      ));
    }
    
    if ($preview) {
      $preview['parent'] = $id;
      $idpreview = $files->additem($preview);
      $files->setvalue($id, 'preview', $idpreview);
    }
    
    $this->added($id);
    return $id;
  }
  
  public function uploadthumbnail($filename, $content) {
    if (!preg_match('/\.(jpg|jpeg|gif|png|bmp)$/i', $filename)) return false;
    $linkgen = tlinkgenerator::i();
    $filename = $linkgen->filterfilename($filename);
    $tempfilename = $this->doupload($filename, $content);
    
    return $this->add(array(
    'filename' => $filename,
    'tempfilename' => $tempfilename,
    'enabledpreview' => false
    ));
  }
  
  //$filename must be specefied before such as  thumb/img004893.jpg
  public function uploadthumb($filename, &$content) {
    $hash = trim(base64_encode(md5($content, true)), '=');
    $files = tfiles::i();
    if (($id = $files->indexof('hash', $hash)) ||
    ($id = $files->getdb('imghashes')->findid('hash = '. dbquote($hash)))) {
      return $id;
    }
    
    if ($image = imagecreatefromstring($content)) {
      if (!strbegin($filename, litepublisher::$paths->files)) $filename = litepublisher::$paths->files. ltrim($filename, '\/');
      $destfilename = self::replace_ext($filename, '.jpg');
      $destfilename = self::makeunique($destfilename);
      if (self::createthumb($image, $destfilename, $this->previewwidth, $this->previewheight, $this->ratio, $this->clipbounds, $this->quality_snapshot)) {
        $info = getimagesize($destfilename);
        $item = $this->getdefaultvalues(str_replace(DIRECTORY_SEPARATOR, '/', substr($destfilename, strlen(litepublisher::$paths->files))));
        $item['media'] = 'image';
        $item['mime'] = $info['mime'];
        $item['width'] = $info[0];
        $item['height'] = $info[1];
        
        $id = $files->additem($item);
        IF ($hash != $files->getvalue($id, 'hash')) {
          $files->getdb('imghashes')->insert(array(
          'id' => $id,
          'hash' => $hash
          ));
        }
        
        $this->added($id);
        return $id;
      }
    }
    return false;
  }
  
  public function getdefaultvalues($filename) {
    return array(
    'parent' => 0,
    'preview' => 0,
    'media' => 'bin',
    'mime' => 'application/octet-stream',
    'filename' => $filename,
    'size' => 0,
    'icon' => 0,
    'idperm' => 0,
    'height' => 0,
    'width' => 0,
    'preview' => 0,
    'title' => '',
    'description' => '',
    'keywords' => ''
    );
  }
  
  public function getinfo($filename) {
    $realfile = litepublisher::$paths->files. str_replace('/', DIRECTORY_SEPARATOR, $filename);
    $result = $this->getdefaultvalues($filename);
    if (preg_match("/\\.($this->videoext)\$/", $filename, $m)) {
      $ext = $m[1];
      $mime = array(
      'mp4' => 'video/mp4',
      'mpe' => 'video/mpeg',
      'mpeg' => 'video/mpeg',
      'mpg' => 'video/mpeg',
      'avi' => 'video/x-msvideo',
      'mov' => 'video/quicktime',
      'ogv' => 'video/ogg',
      'webm' => 'video/webm',
      'flv' => 'video/x-flv',
      'f4v' => 'video/mp4',
      'f4p' => 'video/mp4',
      );
      
      if (isset($mime[$ext])) $result['mime'] = $mime[$ext];
      $result['media'] = 'video';
      return $result;
    }
    
    if ($info = @getimagesize($realfile)) {
      $result['mime'] = $info['mime'];
      $result['media'] = 'application/x-shockwave-flash' == $info['mime'] ? 'flash' : 'image';
      $result['width'] = $info[0];
      $result['height'] = $info[1];
      return $result;
    }
    
    if (preg_match("/\\.($this->audioext)\$/", $filename)) {
      $mime = array(
      'mp3' => 'audio/mpeg',
      'wav' => 'audio/x-wav',
      'flac' => 'audio/ogg',
      'f4a' => 'audio/mp4',
      'f4b' => 'audio/mp4',
      );
      
      $result['mime'] = $mime[strtolower(substr($filename, -3))];
      $result['media'] = 'audio';
      return $result;
    }
    
    if (strend($filename, '.txt')) {
      $result['mime'] = 'text/plain';
      $result['media'] = 'text';
      return $result;
    }
    
    if (strend($filename, '.swf')) {
      $result['media'] = 'flash';
      $result['mime'] = 'application/x-shockwave-flash';
      
      require_once(litepublisher::$paths->libinclude . 'getid3.php');
      require_once(litepublisher::$paths->libinclude . 'getid3.lib.php');
      require_once(litepublisher::$paths->libinclude . 'module.audio-video.swf.php');
      
      $getID3 = new getID3;
      $getID3->option_md5_data        = true;
      $getID3->option_md5_data_source = true;
      $getID3->encoding               = 'UTF-8';
      //$info = $getID3->analyze($realfile);
      $getID3->openfile($realfile);
      $swf = new getid3_swf($getID3);
      $swf->analyze();
      fclose($getID3->fp);
      $info = $getID3->info;
      
      if (!isset($info['error'])) {
        $result['width'] =(int) round($info['swf']['header']['frame_width']  / 20);
        $result['height'] =(int) round($info['swf']['header']['frame_height']  / 20);
        return $result;
      }
    }
    
    return $result;
    
  }
  
  public static function readimage($srcfilename) {
    if (!file_exists($srcfilename)) return false;
    if (!($info = @getimagesize($srcfilename))) return false;
    if (($info[0] == 0) || ($info[1] == 0)) return false;
    
    switch ($info[2]) {
      case 1:
      return @imagecreatefromgif($srcfilename);
      
      case 2:
      return @imagecreatefromjpeg($srcfilename);
      
      case 3:
      return @imagecreatefrompng($srcfilename);
      
      /*
      4 IMAGETYPE_SWF
      5 IMAGETYPE_PSD
      6 IMAGETYPE_BMP
      7 IMAGETYPE_TIFF_II (intel byte order)
      8 IMAGETYPE_TIFF_MM (motorola byte order)
      9 IMAGETYPE_JPC
      10 IMAGETYPE_JP2
      11 IMAGETYPE_JPX
      12  IMAGETYPE_JB2
      13 IMAGETYPE_SWC
      14 IMAGETYPE_IFF
      */
      
      case 15:
      return @imagecreatefromwbmp($srcfilename);
      
      case 16:
      return @imagecreatefromxbm($srcfilename);
    }
    return false;
  }
  
  public static function createsnapshot($srcfilename, $destfilename, $x, $y, $ratio, $clipbounds) {
    if (!($source = self::readimage($srcfilename))) return false;
    $r = self::createthumb($source, $destfilename, $x, $y, $ratio, $clipbounds);
    imagedestroy($source);
    return $r;
  }
  
  public static function createthumb($source, $destfilename, $x, $y, $ratio, $clipbounds, $quality_snapshot) {
    if (!$source) return false;
    $sourcex = imagesx($source);
    $sourcey = imagesy($source);
    if (($x >= $sourcex) && ($y >= $sourcey)) return false;
    
    if ($clipbounds) {
      $ratio = $x / $y;
      if ($sourcex/$sourcey > $ratio) {
        $sourcex = $sourcey *$ratio;
      } else {
        $sourcey = $sourcex /$ratio;
      }
    } elseif ($ratio) {
      $ratio = $sourcex / $sourcey;
      if ($x/$y > $ratio) {
        $x = $y *$ratio;
      } else {
        $y = $x /$ratio;
      }
    }
    
    $dest = imagecreatetruecolor($x, $y);
    imagecopyresampled($dest, $source, 0, 0, 0, 0, $x, $y, $sourcex, $sourcey);
    imagejpeg($dest, $destfilename, $quality_snapshot);
    imagedestroy($dest);
    @chmod($destfilename, 0666);
    return true;
  }
  
  public function getsnapshot($srcfilename, $image) {
    $destfilename = self::replace_ext($srcfilename, '.preview.jpg');
    $destfilename = self::makeunique($destfilename);
    if (self::createthumb($image, $destfilename, $this->previewwidth, $this->previewheight, $this->ratio, $this->clipbounds, $this->quality_snapshot)) {
      @chmod($destfilename, 0666);
      $info = getimagesize($destfilename);
      $result = $this->getdefaultvalues(str_replace(DIRECTORY_SEPARATOR, '/', substr($destfilename, strlen(litepublisher::$paths->files))));
      $result['media'] = 'image';
      $result['mime'] = $info['mime'];
      $result['width'] = $info[0];
      $result['height'] = $info[1];
      return $result;
    }
    return false;
  }
  
  public function resize($filename, $image, $x, $y) {
    $sourcex = imagesx($image);
    $sourcey = imagesy($image);
    if (!$y || !$x || !$sourcex || !$sourcey) return false;
    $ratio = $sourcex / $sourcey;
    if ($x/$y > $ratio) {
      $x = $y *$ratio;
    } else {
      $y = $x /$ratio;
    }
    
    $x = (int) round($x);
    $y = (int) round($y);
    
    $dest = imagecreatetruecolor($x, $y);
    imagecopyresampled($dest, $image, 0, 0, 0, 0, $x, $y, $sourcex, $sourcey);
    $this->onresize($dest);
    imagejpeg($dest, $filename, $this->quality_original);
    imagedestroy($dest);
    @chmod($filename, 0666);
    
    return array(
    'width' =>$x,
    'height' => $y,
    );
  }
  
  private function getaudioinfo($filename) {
    return false;
    /*
    if (!class_exists('getID3')) return false;
    $realfile = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $filename);
    
    // Initialize getID3 engine
    $getID3 = new getID3;
    $getID3->option_md5_data        = true;
    $getID3->option_md5_data_source = true;
    $getID3->encoding               = 'UTF-8';
    
    $info = $getID3->analyze($realfile);
    if (isset($info['error'])) return false;
    
    $result = array (
    'bitrate'  => @$info['audio']['bitrate'],
    'samplingrate'  => @$info['audio']['sample_rate'],
    'channels'  => @$info['audio']['channels'],
    'duration'  => @$info['playtime_seconds'],
    );
    //$result['tags']            = @$info['tags'];
    //$result['comments']        = @$info['comments'];
    return $result;
    */
  }
  
  public function getvideopreview($filename) {
    return 0;
  }
  
}//class