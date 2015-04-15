<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tbackup2dropbox extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['idcron'] = 0;
    $this->data['email'] = '';
    $this->data['password'] = '';
    $this->data['dir'] = '/';
    $this->data['uploadfiles'] = true;
    $this->data['onlychanged'] = false;
    $this->data['posts'] = 0;
    $this->data['comments'] = 0;
    $this->data['useshell'] = false;
  }
  
  public function send() {
    if ($this->password == '') return;
    if ($this->onlychanged) {
      if (($this->posts ==litepublisher::$classes->posts->count) && ($this->comments == litepublisher::$classes->commentmanager->count)) return;
      $this->posts =litepublisher::$classes->posts->count;
      $this->comments = litepublisher::$classes->commentmanager->count;
      $this->save();
    }
    
    $backuper = tbackuper::i();
    $filename  = $this->useshell ? $backuper->createshellbackup() : $backuper->createbackup();
    
    litepublisher::$classes->include_file(litepublisher::$paths->plugins . 'backup2dropbox' . DIRECTORY_SEPARATOR . 'DropboxUploader.php');
    
    $uploader = new DropboxUploader($this->email, $this->password);
    try {
      set_time_limit(600);
      $uploader->upload($filename, $this->dir);
      unlink($filename);
      if ($this->uploadfiles) {
        if ($this->useshell) {
          $filename= $backuper->createshellfilesbackup();
          $uploader->upload($filename, $this->dir);
          unlink($filename);
        } else {
          $this->upload_files($uploader, '');
        }
      }
    } catch (Exception $e) {
      return $e->getMessage();
    }
    return true;
  }
  
  private function upload_files(DropboxUploader $uploader, $dir) {
    if ($dir != '') $dir = trim($dir, '/') . '/';
    if ($list = glob(litepublisher::$paths->files . $dir . '*')) {
      foreach($list as $filename) {
        if (is_dir($filename)) {
          $base = basename($filename);
          if ($base[0] == '.') continue;
          $this->upload_files($uploader, $base);
        } else {
          $uploader->upload($filename, $this->dir . 'files/' . $dir);
        }
      }
    }
    
  }
  
}//class