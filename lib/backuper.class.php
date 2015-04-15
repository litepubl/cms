<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tbackuper extends tevents {
  public $archtype;
  public $result;
  public $tar;
  public $zip;
  public $unzip;
  private $__filer;
  private $existingfolders;
  private $lastdir;
  private $stdfolders;
  private $hasdata;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function include_tar() {
    litepublisher::$classes->include_file(litepublisher::$paths->libinclude . 'tar.class.php');
  }
  
  public static function include_zip() {
    litepublisher::$classes->include_file(litepublisher::$paths->libinclude . 'zip.lib.php');
  }
  
  public static function include_unzip() {
    litepublisher::$classes->include_file(litepublisher::$paths->libinclude . 'strunzip.lib.php');
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'backuper';
    $this->addevents('onuploaded');
    $this->data['ftproot'] = '';
    $this->__filer = false;
    $this->tar = false;
    $this->zip = false;
    $this->unzip = false;
    $this->archtype = 'zip';
    $this->lastdir = '';
    $this->data['filertype'] = 'ftp';
  }
  
  public function __destruct() {
    unset($this->__filer, $this->tar, $this->zip, $this->unzip);
    parent::__destruct();
  }
  
  public function unknown_archive() {
    $this->error('Unknown archive type ' . $this->archtype);
  }
  
  public function load() {
    $result = parent::load();
    if ($this->filertype == 'auto') $this->filertype = self::getprefered();
    return $result;
  }
  
  public static function getprefered() {
    $datafile = litepublisher::$paths->data . 'storage.php';
    if (file_exists($datafile)) {
      $dataowner= fileowner($datafile);
      $libowner = fileowner(dirname(__file__));
      
      if (($libowner !== false) && ($libowner === $dataowner)) return 'file';
    }
    //if (extension_loaded('ssh2') && function_exists('stream_get_contents') ) return 'ssh2';
    if (extension_loaded('ftp')) return 'ftp';
    if (extension_loaded('sockets') || function_exists('fsockopen')) return 'socket';
    return false;
  }
  
  public function getfiler() {
    if ($this->__filer) return $this->__filer;
    switch ($this->filertype) {
      case 'ftp':
      $result = new tftpfiler();
      break;
      
      case 'ssh2':
      $result = new tssh2filer();
      break;
      
      case 'socket':
      $result = new tftpsocketfiler();
      break;
      
      case 'file':
      $result = tlocalfiler::i();
      break;
      
      default:
      $this->filertype = 'file';
      $result = tlocalfiler::i();
      $result->chmod_file = 0666;
      $result->chmod_dir = 0777;
      break;
    }
    
    $this->__filer = $result;
    return $result;
  }
  
  public function connect($host, $login, $password) {
    if ($this->filer->connected) return true;
    if ($this->filer->connect($host, $login, $password)) {
      if (($this->filertype == 'ftp') || ($this->filertype == 'socket')) $this->check_ftp_root();
      return true;
    }
    return false;
  }
  
  public function createarchive() {
    if (!$this->filer->connected) $this->error('Filer not connected');
    switch ($this->archtype) {
      case 'tar':
      self::include_tar();
      $this->tar = new tar();
      break;
      
      case 'zip':
      self::include_zip();
      $this->zip = new zipfile();
      break;
      
      case 'unzip':
      self::include_unzip();
      $this->unzip = new StrSimpleUnzip();
      break;
      
      default:
      $this->unknown_archive();
    }
  }
  
  public function savearchive() {
    switch ($this->archtype) {
      case 'tar':
      $result = $this->tar->savetostring(true);
      $this->tar = false;
      return $result;
      
      case 'zip':
      $result = $this->zip->file();
      $this->zip = false;
      return $result;
      
      default:
      $this->unknown_archive();
    }
  }
  
  private function addfile($filename, $content, $perm) {
    switch ($this->archtype) {
      case 'tar':
      return $this->tar->addstring($content, $filename, $perm);
      
      case 'zip':
      return $this->zip->addFile($content, $filename);
      
      default:
      $this->unknown_archive();
    }
  }
  
  private function adddir($dir, $perm) {
    switch ($this->archtype) {
      case 'tar':
      return $this->tar->adddir($dir, $perm);
      
      case 'zip':
      return true;
      
      default:
      $this->unknown_archive();
    }
  }
  
  private function  readdir($path) {
    $path  = rtrim($path, '/');
    $filer = $this->getfiler();
    if ($list = $filer->getdir($path )) {
      $this->adddir($path, $filer->getchmod($path));
      $path .= '/';
      $hasindex = false;
      foreach ($list as $name => $item) {
        $filename = $path . $name;
        if ($item['isdir']) {
          $this->readdir($filename);
        } 			else {
          if (preg_match('/(\.bak\.php$)|(\.lok$)/',  $name)) continue;
          $this->addfile($filename,$filer->getfile($filename), $item['mode']);
          if (!$hasindex) $hasindex = ($name == 'index.php') || ($name == 'index.htm');
        }
      }
      if (!$hasindex) $this->addfile($path . 'index.htm', '', $filer->chmod_file);
    }
  }
  
  private function readdata($path) {
    $path = rtrim($path, DIRECTORY_SEPARATOR );
    $filer = tlocalfiler::i();
    if ($list = $filer->getdir($path)) {
      $dir = 'storage/data/' . str_replace(DIRECTORY_SEPARATOR  , '/', substr($path, strlen(litepublisher::$paths->data)));
      $this->adddir($dir, $filer->getchmod($path));
      $dir = rtrim($dir, '/') . '/';
      $hasindex = false;
      $path .= DIRECTORY_SEPARATOR ;
      $ignoredir = array('languages', 'logs', 'themes');
      foreach ($list as $name => $item) {
        $filename = $path . $name;
        if (is_dir($filename)) {
          if (($dir == 'storage/data/') && in_array($name, $ignoredir)) {
            $this->adddir($dir . $name . '/', 0777);
            $this->addfile($dir . $name . '/index.htm', '', 0666);
          } else {
            $this->readdata($filename);
          }
        }else {
          if (preg_match('/(\.bak\.php$)|(\.lok$)|(\.log$)/',  $name)) continue;
          $this->addfile($dir . $name, file_get_contents($filename), $item['mode']);
          if (!$hasindex) $hasindex = ($name == 'index.php') || ($name == 'index.htm');
        }
      }
      if (!$hasindex) $this->addfile($dir . 'index.htm', '', $filer->chmod_file);
    }
  }
  
  private function  readhome() {
    $filer = $this->filer;
    $this->chdir(rtrim(litepublisher::$paths->home, DIRECTORY_SEPARATOR ));
    if ($list = $filer->getdir('.')) {
      foreach ($list as $name => $item) {
        if ($item['isdir']) continue;
        $this->addfile($name,$filer->getfile($name), $item['mode']);
      }
    }
  }
  
  public function chdir($dir) {
    if ($dir === $this->lastdir) return;
    $this->lastdir= $dir;
    //if (($this->filertype == 'ftp') || ($this->filertype == 'socket')) {
      if (!($this->__filer instanceof tlocalfiler)) {
        $dir = str_replace('\\', '/', $dir);
        if ('/' != DIRECTORY_SEPARATOR  ) $dir = str_replace(DIRECTORY_SEPARATOR  , '/', $dir);
        $dir = rtrim($dir, '/');
        $root = rtrim($this->ftproot, '/');
        if (strbegin($dir, $root)) $dir = substr($dir, strlen($root));
        $this->filer->chdir($dir);
      } else {
        $this->filer->chdir($dir);
      }
    }
    
    public function setdir($dir) {
      $dir = trim($dir, '/');
      if ($i = strpos($dir, '/')) $dir = substr($dir, 0, $i);
      if (!isset(litepublisher::$paths->$dir)) $this->error(sprintf('Unknown "%s" folder', $dir));
      $this->chdir(dirname(rtrim(litepublisher::$paths->$dir, DIRECTORY_SEPARATOR )));
    }
    
    public function check_ftp_root() {
      $temp = litepublisher::$paths->data . md5rand();
      file_put_contents($temp,' ');
      @chmod($temp, 0666);
      $filename = str_replace('\\\\', '/', $temp);
      $filename = str_replace('\\', '/', $filename);
      $this->filer->chdir('/');
      if (($this->ftproot == '') || !strbegin($filename, $this->ftproot) || !$this->filer->exists(substr($filename, strlen($this->ftproot)))) {
        $this->ftproot = $this->find_ftp_root($temp);
        $this->save();
      }
      unlink($temp);
    }
    
    public function find_ftp_root($filename) {
      $root = '';
      $filename = str_replace('\\\\', '/', $filename);
      $filename = str_replace('\\', '/', $filename);
      if ($i = strpos($filename, ':')) {
        $root = substr($filename, 0, $i);
        $filename = substr($filename, $i);
      }
      
      $this->filer->chdir('/');
      while (($filename != '') && !$this->filer->exists($filename)) {
        if ($i = strpos($filename, '/', 1)) {
          $root .= substr($filename, 0, $i);
          $filename = substr($filename, $i);
        } else {
          return false;
        }
      }
      return $root;
    }
    
    public function getpartial($plugins, $theme, $lib) {
      set_time_limit(300);
      $this->createarchive();
      if (dbversion) $this->addfile('dump.sql', $this->getdump(), $this->filer->chmod_file);
      
      //$this->readdata(litepublisher::$paths->data);
      $this->setdir('storage');
      $this->readdir('storage/data');
      
      if ($lib)  {
        $this->setdir('lib');
        $this->readdir('lib');
        $this->setdir('js');
        $this->readdir('js');
        
        $this->readhome();
      }
      
      if ($theme)  {
        $this->setdir('themes');
        $views = tviews::i();
        $names = array();
        foreach ($views->items as $id => $item) {
          if (in_array($item['themename'], $names))continue;
          $names[] = $item['themename'];
          $this->readdir('themes/' . $item['themename']);
        }
      }
      
      if ($plugins) {
        $this->setdir('plugins');
        $plugins = tplugins::i();
        foreach ($plugins->items as $name => $item) {
          if (@is_dir(litepublisher::$paths->plugins . $name)) {
            $this->readdir('plugins/' . $name);
          }
        }
      }
      
      return $this->savearchive();
    }
    
    public function getfull() {
      set_time_limit(300);
      $this->createarchive();
      if (dbversion) $this->addfile('dump.sql', $this->getdump(), $this->filer->chmod_file);
      
      //$this->readdata(litepublisher::$paths->data);
      $this->setdir('storage');
      $this->readdir('storage/data');
      
      $this->setdir('lib');
      $this->readdir('lib');
      $this->setdir('js');
      $this->readdir('js');
      $this->readhome();
      
      $this->setdir('plugins');
      $this->readdir('plugins');
      
      $this->setdir('themes');
      $this->readdir('themes');
      
      return $this->savearchive();
    }
    
    public function getdump() {
      $dbmanager = tdbmanager ::i();
      return $dbmanager->export();
    }
    
    public function setdump(&$dump) {
      $dbmanager = tdbmanager ::i();
      return $dbmanager->import($dump);
    }
    
    public function uploaddump($s, $filename) {
      if (strend($filename, '.zip')) {
        self::include_unzip();
        $unzip = new StrSimpleUnzip ();
        $unzip->ReadData($s);
        foreach ($unzip->Entries as  $item) {
          if ($item->Error != 0) continue;
          if (strend($item->Name, '.sql')) {
            $s = $item->Data;
            break;
          }
        }
        unset($unzip);
      } elseif (strend($filename, '.tar.gz') || strend($filename, '.tar')) {
        self::include_tar();
        $tar = new tar();
        $tar->loadfromstring($s);
        foreach ($tar->files as $item) {
          if (!strend($item['name'],'.sql')) {
            $s = $item['file'];
            break;
          }
        }
        unset($tar);
      } else {
        if($s[0] == chr(31) && $s[1] == chr(139) && $s[2] == chr(8)) {
          $s = gzinflate(substr($s,10,-4));
        }
      }
      return $this->setdump($s);
    }
    
    private function writedata($filename, $content, $mode) {
      if (strend($filename, '/.htaccess')) return true;
      if (strend($filename, '/index.htm')) return true;
      $this->hasdata = true;
      $filename = substr($filename, strlen('storage/data/'));
      $filename =str_replace('/', DIRECTORY_SEPARATOR, $filename);
      $filename = litepublisher::$paths->storage . 'newdata' . DIRECTORY_SEPARATOR . $filename;
      tfiler::forcedir(dirname($filename));
      if (file_put_contents($filename, $content) === false) return false;
      @chmod($filename, $mode);
      return true;
    }
    
    public function uploadfile($filename, $content, $mode) {
      $filename = ltrim($filename, '/');
      if (dbversion && $filename == 'dump.sql') {
        $this->setdump($content);
        return true;
      }
      
      $mode = $this->filer->getmode($mode);
      
      //ignore home files
      if (!strpos($filename, '/')) return true;
      //spec rule for storage folder
      if (strbegin($filename, 'storage/')) {
        if (strbegin($filename, 'storage/data/')) return $this->writedata($filename, $content, $mode);
        return true;
      }
      
      $dir = rtrim(dirname($filename), '/');
      $this->setdir($dir);
      if (!isset($this->existingfolders[$dir])) {
        $this->filer->forcedir($dir);
        $this->existingfolders[$dir] = true;
      }
      
      if ($this->filer->putcontent($filename, $content) === false) return false;
      $this->filer->chmod($filename, $mode);
      return true;
    }
    
    public function upload($content, $archtype) {
      set_time_limit(300);
      if ($archtype == 'zip') $archtype = 'unzip';
      $this->archtype = $archtype;
      $this->hasdata = false;
      $this->existingfolders = array();
      $this->createarchive();
      
      switch ($archtype) {
        case 'tar':
        $this->tar->loadfromstring($content);
        if (!is_array($this->tar->files)) {
          $this->tar = false;
          return $this->errorarch();
        }
        
        foreach ($this->tar->files as $item) {
          if (!$this->uploadfile($item['name'],$item['file'], $item['mode'])) return $this->errorwrite($item['name']);
        }
        $this->onuploaded($this);
        $this->tar = false;
        break;
        
        case 'unzip':
        $mode = $this->filer->chmod_file;
        $this->unzip->ReadData($content);
        foreach ($this->unzip->Entries as  $item) {
          if ($item->Error != 0) continue;
          if (!$this->uploadfile($item->Path . '/' . $item->Name, $item->Data, $mode))
          return $this->errorwrite($item->Path . $item->Name);
        }
        $this->onuploaded($this);
        $this->unzip = false;
        break;
        
        default:
        $this->unknown_archive();
      }
      $this->existingfolders= false;
      if ($this->hasdata) $this->renamedata();
      return true;
    }
    
    private function renamedata() {
      if (!is_dir(litepublisher::$paths->backup)) {
        mkdir(litepublisher::$paths->backup, 0777);
        @chmod(litepublisher::$paths->backup, 0777);
      }
      $backup  = litepublisher::$paths->backup . 'data-' . time();
      $data =rtrim(litepublisher::$paths->data, DIRECTORY_SEPARATOR);
      rename($data, $backup);
      rename(litepublisher::$paths->storage . 'newdata', $data);
      tfiler::delete($backup, true, true);
    }
    
    private function errorwrite($filename) {
      $lang = tlocal::admin('service');
      $this->result = sprintf($lang->errorwritefile, $filename);
      return false;
    }
    
    private function errorarch() {
      $lang = tlocal::admin('service');
      $this->result = $lang->errorarchive;
      return false;
    }
    
    //upload plugin or theme
    public function uploaditem($content, $archtype, $itemtype = false) {
      set_time_limit(300);
      if ($archtype == 'zip') $archtype = 'unzip';
      $this->archtype = $archtype;
      $this->existingfolders = array();
      $this->createarchive();
      switch ($archtype) {
        case 'tar':
        $this->tar->loadfromstring($content);
        if (!is_array($this->tar->files)) {
          $this->tar = false;
          return $this->errorarch();
        }
        
        foreach ($this->tar->files as $item) {
          if (strbegin($item['name'], 'themes/') || strbegin($item['name'], 'plugins/')){
            if (!$this->uploadfile($item['name'],$item['file'], $item['mode'])) return $this->errorwrite($item['name']);
          }
        }
        //$this->onuploaded($this);
        $this->tar = false;
        break;
        
        case 'unzip':
        $mode = $this->filer->chmod_file;
        $this->unzip->ReadData($content);
        foreach ($this->unzip->Entries as  $item) {
          if ($item->Error != 0) continue;
          $filename = $item->Path . '/' . $item->Name;
          if (strbegin($filename, 'themes/') || strbegin($filename, 'plugins/')){
            if (!$this->uploadfile($filename, $item->Data, $mode)) return $this->errorwrite($item->Path . $item->Name);
          }
        }
        //$this->onuploaded($this);
        $this->unzip = false;
        break;
        
        default:
        $this->unknown_archive();
      }
      $this->existingfolders = false;
      return true;
    }
    
    public function unpack($content, $archtype) {
      $result = array();
      if ($archtype == 'zip') $archtype = 'unzip';
      $this->archtype = $archtype;
      //$this->createarchive();
      switch ($archtype) {
        case 'tar':
        self::include_tar();
        $tar = new tar();
        $tar->loadfromstring($content);
        if (!is_array($tar->files)) {
          unset($tar);
          return $this->errorarch();
        }
        
        foreach ($tar->files as $item) {
          $result[$item['name']] = $item['file'];
        }
        unset($tar);
        break;
        
        case 'unzip':
        case 'zip':
        self::include_unzip();
        $unzip = new StrSimpleUnzip();
        $unzip->ReadData($content);
        foreach ($unzip->Entries as  $item) {
          $result[$item->Path . '/' . $item->Name] = $item->Data;
        }
        unset($unzip);
        break;
        
        default:
        $this->unknown_archive();
      }
      
      return $result;
    }
    
    public function createfullbackup(){
      return $this->_savebackup($this->getpartial(true, true, true));
    }
    
    public function createbackup(){
      /*
      $filer = $this->__filer;
      if (!$filer || ! ($filer instanceof tlocalfiler)) {
        $this->__filer = tlocalfiler::i();
      }
      */
      $result = $this->_savebackup($this->getpartial(false, false, false));
      //$this->__filer = $filer;
      return $result;
    }
    
    public function getfilename($ext) {
      $filename = litepublisher::$paths->backup . litepublisher::$domain . date('-Y-m-d');
      $result = $filename . $ext;
      $i = 2;
      while (file_exists($result) && ($i < 100)) {
        $result = $filename  . '_' . $i++ . $ext;
      }
      return $result;
    }
    
    private function _savebackup($s) {
      $filename = $this->getfilename($this->archtype == 'zip' ? '.zip' : '.tar.gz');
      file_put_contents($filename, $s);
      @chmod($filename, 0666);
      return $filename;
    }
    
    public function getshellfilename() {
      $filename = $this->getfilename('.tar.gz');
      return substr(substr($filename, 0, strlen($filename) - strlen('.tar.gz')), strrpos($filename, DIRECTORY_SEPARATOR) + 1);
    }
    
    public function createshellbackup(){
      $dbconfig = litepublisher::$options->dbconfig;
      $cmd = array();
      $cmd[] = 'cd ' . litepublisher::$paths->backup;
      $cmd[] = sprintf('mysqldump -u%s -p%s %s>dump.sql', $dbconfig['login'], str_rot13(base64_decode($dbconfig['password'])), $dbconfig['dbname']);
      $filename = $this->getshellfilename();
      $cmd[] = sprintf('tar --exclude="*.bak.php" --exclude="*.lok" --exclude="*.log" -cf %s.tar ../../storage/data/* dump.sql', $filename);
      $cmd[] ='rm dump.sql';
      $cmd[] = "gzip $filename.tar";
      $cmd[] = "rm $filename.tar";
      $cmd[] = "chmod 0666 $filename.tar.gz";
      exec(implode("\n", $cmd), $r);
      //echo implode("\n", $r);
      return litepublisher::$paths->backup . $filename . '.tar.gz';
    }
    
    public function createshellfullbackup(){
      $dbconfig = litepublisher::$options->dbconfig;
      $cmd = array();
      $cmd[] = 'cd ' . litepublisher::$paths->backup;
      $cmd[] = sprintf('mysqldump -u%s -p%s %s>dump.sql', $dbconfig['login'], str_rot13(base64_decode($dbconfig['password'])), $dbconfig['dbname']);
      $filename = $this->getshellfilename();
      $cmd[] = sprintf('tar --exclude="*.bak.php" --exclude="*.lok" --exclude="*.log" -cf %s.tar ../../storage/data/* dump.sql ../../lib/* ../../plugins/* ../../themes/* ../../js/* ../../index.php "../../.htaccess"', $filename);
      $cmd[] ='rm dump.sql';
      $cmd[] = "gzip $filename.tar";
      $cmd[] = "rm $filename.tar";
      $cmd[] = "chmod 0666 $filename.tar.gz";
      exec(implode("\n", $cmd), $r);
      //echo implode("\n", $r);
      return litepublisher::$paths->backup . $filename . '.tar.gz';
    }
    
    public function createshellfilesbackup(){
      $cmd = array();
      $cmd[] = 'cd ' . litepublisher::$paths->backup;
      $filename = 'files_' . litepublisher::$domain . date('-Y-m-d');
      $cmd[] = sprintf('tar --exclude="*.bak.php" --exclude="*.lok" --exclude="*.log" -cf %s.tar ../../files/*', $filename);
      $cmd[] = "gzip $filename.tar";
      $cmd[] = "rm $filename.tar";
      $cmd[] = "chmod 0666 $filename.tar.gz";
      exec(implode("\n", $cmd), $r);
      //echo implode("\n", $r);
      return litepublisher::$paths->backup . $filename . '.tar.gz';
    }
    
    public function test() {
      if (!@file_put_contents(litepublisher::$paths->data . 'index.htm', ' ')) return false;
      if (!$this->filer->connected) return false;
      $this->setdir('lib');
      return $this->uploadfile('lib/index.htm', ' ', $this->filer->chmod_file);
    }
    
    public function getfiletype() {
      if ($this->archtype == 'zip') return '.zip';
      if ($this->archtype == 'tar') return '.tar.gz';
      return false;
    }
    
    public function getarchtype($filename) {
      if (strend($filename, '.zip')) return 'zip';
      if (strend($filename, '.tar.gz') || strend($filename, '.tar')) return 'tar';
      return false;
    }
    
  }//class