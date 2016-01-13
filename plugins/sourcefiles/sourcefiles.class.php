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
if (!$s)) return false;

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


if (!$this->loaditem($this->getfilename($url)) {
while ($url && $url != '/') {
$url = dirname($url);
if (file_exists($this->getfilename($url . '/')) {
return litepublisher::$urlmap->redir($this->url . $url . '/');
}
}

return 404;
}

  }
  
  public function gettitle() {
    $result = $this->item['dir'];
    $result .= $this->item['filename'] == '' ?  '' : '/'. $this->item['filename'];
    return $result;
  }
  
  public function getkeywords() {
    return $this->item['filename'];
  }
  
public function getdescription() { }
public function gethead() { }
  
  public function getidview() {
    return 1;
  }
  
  public function setidview($id) {
  }
  
  public function getcont() {
    $dir = $this->item['dir'];
    $filename = $this->item['filename'];
    $updir = $filename == '' ? '' :
    ($dir == '' ? '' : sprintf('<ul><li><a href="%1$s/source/%2$s/" title="%2$s">..</a></li></ul>', litepublisher::$site->url, $dir));
    
    $theme = ttheme::i();
    return $theme->simple($updir . $this->getcachecontent($dir, $filename));
  }
  
  public function getfilecontent($dir, $filename) {
    if ($filename == '') return $this->getdircontent($dir);
    
    $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
    $realdir = $this->root . $dir;
    return $this->syntax($realdir . DIRECTORY_SEPARATOR. $filename);
  }
  
  public function add($dir, $filename) {
    $dir = trim(str_replace(DIRECTORY_SEPARATOR, '/', $dir), '/');
    if ($id = $this->db->findid(sprintf('filename = %s and dir = %s', dbquote($filename), dbquote($dir)))) return $id;
    
    $item = array(
    'idurl' => 0,
    'filename' => $filename,
    'dir' => $dir
    );
    
    $id =$this->db->add($item);
    if ($dir != '') $dir .= '/';
    $idurl = litepublisher::$urlmap->add("/source/$dir$filename", get_class($this), $id);
    $this->db->setvalue($id, 'idurl', $idurl);
    return $id;
  }
  
  public function syntax($filename) {
    if (strend($filename, '.php')) return highlight_file($filename , true);
    $source = file_get_contents($filename);
    $ext = substr($filename, -3);
    if ($ext == 'tml') $ext = 'htm';
    
    if (!isset($this->geshi)) {
      define('GESHI_ROOT', litepublisher::$paths->plugins . 'sourcefiles' . DIRECTORY_SEPARATOR);
      litepublisher::$classes->include_file(litepublisher::$paths->plugins . 'sourcefiles' .DIRECTORY_SEPARATOR . 'geshi.php');
      $this->geshi = new GeSHi();
      $this->geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
    }
    
    $lang = $this->geshi->get_language_name_from_extension($ext);
    $this->geshi->set_language($lang);
    $this->geshi->set_source($source);
    return $this->geshi->parse_code();
  }
  
  public function getdircontent($dir) {
    $list = $this->getfilelist($dir);
    if (!$list) return '';
    $result = '';
    $url = litepublisher::$site->url;
    if ($dir != '') {
      $updir = dirname($dir);
      $updir = $updir == '.' ? '' : $updir . '/';
      $result = sprintf('<li><a href="%1$s/source/%2$s"><strong>..</strong></a></li>', $url, $updir);
      $dir .= '/';
    }
    
    foreach ($list['dirs'] as $filename) {
      $result .= sprintf('<li><a href="%1$s/source/%2$s/" title="%3$s"><strong>%3$s</strong></a></li>',
      $url, $dir . $filename, strtoupper($filename));
    }
    
    foreach ($list['files'] as $filename) {
      if (preg_match('/\.(php|tml|css|ini|sql|js|txt)$/', $filename)) {
        $result .= sprintf('<li><a href="%1$s/source/%2$s%3$s" title="%3$s">%3$s</a></li>',
        $url, $dir, $filename);
      } elseif (preg_match('/\.(jpg|gif|png|bmp|ico)$/', $filename)) {
        $result .= sprintf('<li><img src="%1$s/%2$s%3$s" alt="%3$s" /></li>', $url, $dir, $filename);
      }
    }
    
    return sprintf('<ul>%s</ul>', $result);
  }
  
  public function getfilelist($dir){
    $dir = trim(str_replace(DIRECTORY_SEPARATOR, '/', $dir), '/');
    $realdir = $this->root . str_replace('/', DIRECTORY_SEPARATOR, $dir) . DIRECTORY_SEPARATOR;
    if ($list = scandir ($realdir)) {
      $result = array (
      'dirs' => array(),
      'files' => array()
      );
      foreach ($list as $i => $filename) {
        if (preg_match('/^(\.|\.\.|\.htaccess|index\.htm|\.svn)$/', $filename) ||
        in_array($dir . '/' . $filename, $this->ignore)) continue;
        if (is_dir($realdir . $filename)) {
          $result['dirs'][] = $filename;
        } else {
          if (strend($filename, '.min.js')) continue;
          $result['files'][] = $filename;
        }
      }
      return $result;
    }
    return false;
  }
  
  public function adddir($dir) {
    $dir = trim(str_replace(DIRECTORY_SEPARATOR, '/', $dir), '/');
    $dirs = array();
    $files = array();
    if ($list = $this->getfilelist($dir)) {
      foreach ($list['dirs'] as $filename) {
        $newdir = $dir . '/' . $filename;
        $dirs[] = litepublisher::$db->escape($filename);
        $this->adddir($newdir);
      }
      
      foreach ($list['files'] as $filename) {
        if (preg_match('/\.(php|tml|css|ini|sql|js|txt)$/', $filename)) {
          $files[] = dbquote($filename);
          $this->add($dir, $filename);
        }
      }
      
    }
    
    $sql = sprintf("(dir = %s and filename <> '' ", dbquote($dir));
    $sql .= count($files) == 0 ?  ')' : sprintf(' and filename not in (%s))', implode(',', $files));
    if ($dir == '') {
      $sql .= count($dirs) == 0 ? ')' :
      sprintf(' or (filename = \'\' and dir <> \'\' and (dir not regexp \'^(%s)($|\\\/)\') )',
      implode('|', $dirs));
    } else {
      $sqldir = litepublisher::$db->escape($dir);
      $sql .= sprintf(' or (filename = \'\' and dir != \'%1$s\' and left(dir, %2$d) = \'%1$s\'', $sqldir, strlen($sqldir));
      $sql .= count($dirs) == 0 ? ')' :
      sprintf(' and (SUBSTRING(dir, %d) not regexp \'^(%s)($|\\\/)\') )',
      strlen($sqldir) + 2, implode('|', $dirs));
    }
    
    if ($deleted = $this->db->getitems($sql)) {
      $items = array();
      $idurls = array();
      foreach ($deleted as $item) {
        $items[] = $item['id'];
        $idurls[] = $item['idurl'];
      }
      
      litepublisher::$urlmap->db->deleteitems($idurls);
      $this->db->deleteitems($items);
    }
    
    if ($id = $this->db->findid("filename = '' and dir = ". dbquote($dir)))  return $id;
    
    $item = array(
    'idurl' => 0,
    'filename' => '',
    'dir' => $dir
    );
    $id = $this->db->add($item);
    if ($dir == '') {
      if ($urlitem = litepublisher::$urlmap->db->finditem("url = '/source/'")) {
        $idurl = $urlitem['id'];
      } else {
        $idurl = litepublisher::$urlmap->add("/source/", get_class($this), $id);
      }
    } else {
      $idurl = litepublisher::$urlmap->add("/source/$dir/", get_class($this), $id);
      //echo "/source/$dir/ added<br>";
    }
    $this->db->setvalue($id, 'idurl', $idurl);
    return $id;
  }
  
  public function reread() {
    $this->adddir('');
    tfiler::delete(litepublisher::$paths->data . 'sourcefiles', true, false);
    litepublisher::$urlmap->clearcache();
  }
  
}//class