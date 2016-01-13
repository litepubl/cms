<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tsourcefiles extends tplugin implements itemplate {
public $item;
  public $geshi;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->data['url'] = '/source/';
    $this->data['idview'] = 1;
  }

public function getdir() {
return litepublisher::$paths->data . 'sourcecache';
}

public function getfilename($url) {
return $this->dir . '/' . md5($url) . '.txt';
}

public function clear() {
tfiler::delete($this->dir, true, false);
}

public function loaditem($filename) {
if (!file_exists($filename)) return false;
$s = file_get_contents($filename);
if (!$s) return false;

$this->item = unserialize($s);
return true;
}

public function saveitem($filename, $data) {
file_put_contents($filename, serialize($data));
@chmod($filename, 0666);
}
  
  public function request($arg) {
$url = substr(litepublisher::$urlmap->url, strlen($this->url));
if (!$url) $url = '/';


if (!$this->loaditem($this->getfilename($url))) {
while ($url && $url != '/') {
$url = dirname($url);
if (file_exists($this->getfilename($url . '/'))) {
return litepublisher::$urlmap->redir($this->url . $url . '/');
}
}

return 404;
}

  }
  
  public function getidview() {
    return $this->data['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->idview) {
      $this->data['idview'] = $id;
      $this->save();
    }
  }

  public function getview() {
    return tview::getview($this);
  }
  
  public function gettitle() {
return $this->item['filename'];
  }
  
  public function getkeywords() {}
 public function getdescription() { }
public function gethead() { }
  
  public function getcont() {
    $dir = $this->item['dir'];
    $filename = $this->item['filename'];
    $updir = $filename == '' ? '' :
    ($dir == '' ? '' : sprintf('<ul><li><a href="%1$s/source/%2$s/" title="%2$s">..</a></li></ul>', litepublisher::$site->url, $dir));
    
    $theme = ttheme::i();
    return $theme->simple($updir . $this->getcachecontent($dir, $filename));
  }

public function creategeshi() {
    if (!isset($this->geshi)) {
      define('GESHI_ROOT', dirname(__file__) . '/');
require(dirname(__file__) . '/geshi.php');
      $this->geshi = new GeSHi();
      $this->geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
    }
}
  
  public function syntax($ext, $content) {
$ext = strtolower($ext);

    if ($ext == 'php') {
return highlight_string ($content);
}

    if ($ext == 'tml') $ext = 'htm';
    

    $lang = $this->geshi->get_language_name_from_extension($ext);
dumpvar($lang);
die($ext);
    $this->geshi->set_language($lang);
    $this->geshi->set_source($source);
    return $this->geshi->parse_code();
  }
  
  public function readzip($zipname) {
        $zip = new ZipArchive ();
        if ($zip->open($zipname) !== true) {
$this->error(sprintf('Error open "%s" zip archive', $zipname));
        }

$this->creategeshi();
$root = false;        
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
if (strend($filename, '.min.js') || strend($filename, '.min.css')) continue;

            if (!$root) {
      $list = explode('/', trim($filename, '/'));
$root = $list[0];
            }
            
            $filename = ltrim(substr(ltrim($filename, '/'), strlen($root)), '/');
$ext = strtolower(substr($filename,            strrpos($filename, '.') + 1));

$content = $zip->getFromIndex($i);
if (!$content) continue;

$this->saveitem($this->getfilename($filename), array(
'filename' => $filename,
'content' => $this->syntax($ext, $content),
));
        }
        
        $zip->close();
}

}//class