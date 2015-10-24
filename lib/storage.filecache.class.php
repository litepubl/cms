<?php

class tfilecache {
  
  public function clear() {
    $path = litepublisher::$paths->cache;
    if ( $h = @opendir($path)) {
      while(FALSE !== ($filename = @readdir($h))) {
        if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
        $file = $path. $filename;
        if (is_dir($file)) {
          tfiler::delete($file . DIRECTORY_SEPARATOR, true, true);
        } else {
          unlink($file);
        }
      }
      closedir($h);
    }
  }
  
  public function set($filename, $data) {
    $fn = litepublisher::$paths->cache . $filename;
    if (!is_string($data)) $data = serialize($data);
    file_put_contents($fn, $data);
    @chmod($fn, 0666);
  }
  
  public function get($filename) {
    $fn = litepublisher::$paths->cache . $filename;
    if (file_exists($fn)) return  file_get_contents($fn);
    return false;
  }
  
  public function delete($filename) {
    $fn = litepublisher::$paths->cache . $filename;
    if (file_exists($fn)) unlink($fn);
  }
  
  public function exists($filename) {
    return file_exists(litepublisher::$paths->cache . $filename);
  }
  
}//class