<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsourcefiles  {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tsourcefiles::i();
    $html = tadminhtml::i();
    $args = targs::i();
    $args->root = $plugin->root;
    $args->formtitle = 'Source files option';
    $args->data['$lang.root'] = 'Path to source files';
    $result = $html->adminform('[text=root]', $args);
    
    $result .= '<form name="rereadform" action="" method="post" >
    <p><input type="submit" name="reread" value="Reread"/></p>
    <p><input type="submit" name="download" value="Download and refresh"/></p>
    </form>';
    
    return $result;
  }
  
  public function processform() {
    $plugin = tsourcefiles::i();
    if (isset($_POST['download'])) {
      set_time_limit(300);
      $version = litepublisher::$options->version;
      if (!(
      //($s = http::get("http://dest/build/litepublisher.4.22.tar.gz")) ||
      ($s = http::get("http://litepublisher.googlecode.com/files/litepublisher.$version.tar.gz")) ||
      ($s = http::get("http://litepublisher.com/download/litepublisher.$version.tar.gz"))
      )) {
        return  'Error download';
      }
      
      tbackuper::include_tar();
      $tar = new tar();
      $tar->loadfromstring($s);
      if (!is_array($tar->files)) {
        unset($tar);
        return 'Invalid file archive';
      }
      tfiler::delete($plugin->root, true, false);
      foreach ($tar->files as $item) {
        $filename = $plugin->root . $item['name'];
        $dir = dirname($filename);
        if (!is_dir($dir)) $this->mkdir($dir);
        
        file_put_contents($filename, $item['file']);
        @chmod($filename,0666);
      }
      
      unset($tar);
      $plugin->reread();
    } elseif (isset($_POST['reread'])) {
      $plugin->reread();
    } else {
      $plugin->root = $_POST['root'];
      $plugin->save();
    }
  }
  
  public function mkdir($dir) {
    $up = dirname($dir);
    if (!is_dir($up)) $this->mkdir($up);
    mkdir($dir, 0777);
    chmod($dir, 0777);
  }
  
}//class