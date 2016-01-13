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

return unserialize($s);
}

public function saveitem($filename, $data) {
file_put_contents($filename, serialize($data));
@chmod($filename, 0666);
}
  
  public function request($arg) {
$url = substr(litepublisher::$urlmap->url, strlen($this->url));
if (!$url) $url = '/';

if (!($this->item = $this->loaditem($this->getfilename($url)))) {
while ($url && $url != '/') {
$url = dirname($url);
if ($url == '.') {
return litepublisher::$urlmap->redir($this->url);
}

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
$result = '';
if ($this->item['type'] == 'file') {
$dir = dirname($this->item['filename']);
if ($dir == '.') {
$dir = '/';
} else {
$dir .= '/';
}
if ($item = $this->loaditem($this->getfilename($dir))) {
$result .= $item['content'];
}
}
    
$result .= $this->item['content'];
    return $this->view->theme->simple($result);
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
return highlight_string ($content, true);
}

switch ($ext) {
case 'tml':
$lang = 'html5';
break;

case 'less':
$lang = 'css';
break;

case 'js':
$lang = 'jquery';
break;

default:
    $lang = $this->geshi->get_language_name_from_extension($ext);
}

    $this->geshi->set_language($lang);
    $this->geshi->set_source($content);
    return $this->geshi->parse_code();
  }
  
  public function readzip($zipname) {
$zip = new zipArchive();
        if ($zip->open($zipname) !== true) {
$this->error(sprintf('Error open "%s" zip archive', $zipname));
        }

$this->creategeshi();
$dirlist = array();
$root = false;        
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
if (preg_match('/\.(min\.js|min\.css|jpg|jpeg|ico|png|gif|svg|swf|xap|otf|eot|ttf|woff|woff2)$/', $filename)) continue;

            if (!$root) {
      $list = explode('/', trim($filename, '/'));
$root = $list[0];
            }
            
            $filename = ltrim(substr(ltrim($filename, '/'), strlen($root)), '/');
$ext = strtolower(substr($filename,            strrpos($filename, '.') + 1));
$content = trim($zip->getFromIndex($i));
if (!$content) continue;

$path = dirname($filename);
if (isset($dirlist[$path])) {
$dirlist[$path][] = basename($filename);
} else {
$dirlist[$path] = array(basename($filename));
}

$this->saveitem($this->getfilename($filename), array(
'type' => 'file',
'filename' => $filename,
'content' => $this->syntax($ext, $content),
));
        }
        
        $zip->close();

$tml = '<li><a href="' . litepublisher::$site->url . $this->url . '%s">%s</a></li>';
$tml_list = '<ul>%s</ul>';
$dirnames = array_keys($dirlist);
foreach ($dirlist as $dir => $filelist) {
$list = '';
if ($dir != '.') {
$list .= sprintf($tml, dirname($dir) == '.' ? '' : dirname($dir), '..');
}

$subdirs = array();
foreach ($dirnames as $i => $subdir) {
if ($dir == dirname($subdir)) {
$subdirs[] = basename($subdir);
unset($subdirs[$i]);
}
}

sort($subdirs, SORT_NATURAL);

foreach ($subdirs as $subdir) {
$list .= sprintf($tml, ($dir == '.' ? '' : $dir . '/') . $subdir . '/', strtoupper($subdir));
}

sort($filelist, SORT_NATURAL);
foreach ($filelist as $filename) {
$list .= sprintf($tml, $filename, $filename);
}

$this->saveitem($this->getfilename($dir == '.' ? '/' : $dir . '/'), array(
'type' => 'dir',
'filename' => $dir,
'content' => sprintf($tml_list, $list),
));
}
}

}//class