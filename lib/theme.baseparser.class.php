<?php

class baseparser extends tevents {
  public $theme;
  public $tagfiles;
  public $paths;
  public $extrapaths;
  protected $abouts;
  protected $pathmap;
  protected $parsedtags;
  
  protected function create() {
    parent::create();
    $this->basename = 'baseparser';
    $this->addevents('ongetpaths', 'beforeparse', 'parsed', 'onfix');
    $this->addmap('tagfiles', array());
    $this->addmap('extrapaths', array());
    $this->data['replacelang'] = false;
    $this->data['removephp'] = true;
    
    $this->pathmap = array();
  }
  
  public function parse(basetheme $theme) {
    $theme->lock();
    $this->checkparent($theme->name);
    
    $about = $this->getabout($theme->name);

    switch ($about['type']) {
      case 'litepublisher3':
      case 'litepublisher':
      $this->error('Litepublisher not supported old themes');
      break;
      
      case 'litepublisher4':
      case '6':
      $theme->type = 'litepublisher';
      $this->parsetheme($theme);
      break;
    }
    
    $this->parsed($theme);
    if ($this->replacelang) $this->doreplacelang($theme);
    $theme->unlock();
    return true;
  }
  
  public function doreplacelang(basetheme $theme) {
    $lang = tlocal::i('comment');
    foreach ($theme->templates as $name => $value) {
      if (is_string($value)) {
        $theme->templates[$name] = $theme->replacelang($value, $lang);
      }
    }
}
    
  public function callback_replace_php(array $m) {
    return strtr($m[0], array(
    '$' => '&#36;',
    '?' => '&#63;',
    '(' => '&#40;',
    ')' => '&#41;',
    '[' => '&#91;',
    ']' => '&#93;',
    '{' => '&#123;',
    '}' => '&#125;'
    ));
  }
  
  public function callback_restore_php($m) {
    return strtr($m[0], array(
    '&#36;' => '$',
    '&#63;' => '?',
    '&#40;' => '(',
    '&#41;' => ')',
    '&#91;' => '[',
    '&#93;' => ']',
    '&#123;' => '{',
    '&#125;' => '}'
    ));
  }
  
  public function parsetheme(basetheme $theme) {
    $about = $this->getabout($theme->name);
    $filename = litepublisher::$paths->themes . $theme->name . DIRECTORY_SEPARATOR . $about['file'];
    if (!file_exists($filename))  return $this->error("The requested theme '$theme->name' file $filename not found");
    
    if ($theme->name != 'default') {
      if ($theme->name == 'default-old') {
        $parentname = 'default';
      } else {
        $parentname = empty($about['parent']) ? 'default-old' : $about['parent'];
      }
      
      $parent = ttheme::getinstance($parentname);
      $theme->templates = $parent->templates;
      $theme->parent = $parent->name;
    }
    
    $this->parsedtags = array();
    
    $s = $this->getfile($filename, $about);
    $this->parsetags($theme, $s);
    $this->afterparse($theme);
  }
  
  public function getfile($filename, $about) {
    $s = file_get_contents($filename);
    if ($s === false) return $this->error(sprintf('Error read "%s" file', $filename));
    
    $s = strip_utf($s);
    $s = str_replace(array("\r\n", "\r", "\n\n"), "\n", $s);
    
    //strip coments
    $s = preg_replace('/\s*\/\*.*?\*\/\s*/sm', "\n", $s);
    $s = preg_replace('/^\s*\/\/.*?$/m', '', $s);
    
    //normalize tags
    $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', $s);
    
    //replace $about.*
    if (preg_match_all('/\$about\.(\w\w*+)/', $s, $m, PREG_SET_ORDER)) {
      $a = array();
      foreach ($m as $item) {
        $name = $item[1];
        if (isset($about[$name])) {
          $a[$item[0]] = $about[$name];
        }
      }
      $s = strtr($s, $a);
    }
    
    return trim($s);
  }
  
  public function getabout($name) {
    if (!isset($this->abouts)) $this->abouts = array();
    if (!isset($this->abouts[$name])) {
      $filename = litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR . 'about.ini';
      if (file_exists($filename) && (      $about = parse_ini_file($filename, true))) {
        if (empty($about['about']['type'])) $about['about']['type'] = 'litepublisher3';
        //join languages
        if (isset($about[litepublisher::$options->language])) {
          $about['about'] = $about[litepublisher::$options->language] + $about['about'];
        }
        
        $this->abouts[$name] = $about['about'];
      } else {
        $this->abouts[$name] = false;
      }
    }
    return $this->abouts[$name];
  }
  
  public function checkparent($name) {
    if ($name == 'default') return true;
    
    $about = $this->getabout($name);
    $parents = array($name);
    while (!empty($about['parent'])) {
      $name = $about['parent'];
      if (in_array($name, $parents)) {
        $this->error(sprintf('Theme circle "%s"', implode(', ', $parents)));
      }
      
      $parents[] = $name;
      $about = $this->getabout($name);
    }
    
    return true;
  }
  
  public function reparse() {
    $theme = ttheme::i();
    $theme->lock();
    $this->parse($theme);
    ttheme::clearcache();
    $theme->unlock();
  }
  
  //4 ver
  public static function find_close($s, $a) {
    $brackets = array(
    '[' => ']',
  '{' => '}',
    '(' => ')'
    );
    
    $b = $brackets[$a];
    $i = strpos($s, $b);
    $sub = substr($s, 0, $i);
    if (substr_count($sub, $a) == 0) return $i;
    
    while (substr_count($sub, $a) >  substr_count($sub, $b)) {
      $i = strpos($s, $b, $i + 1);
      if ($i === false) die(" The '$b' not found in\n$s");
      $sub = substr($s, 0, $i);
    }
    
    return $i;
  }
  
  public function parsetags(basetheme $theme, $s) {
    $this->theme = $theme;
    if (!$this->paths || !count($this->paths)) {
      $this->paths = $this->loadpaths();
    }
    
    $s = trim($s);
    $this->callevent('beforeparse', array($theme, &$s));

    if ($this->removephp) {
      $s = preg_replace('/\<\?.*?\?\>/ims', '', $s);
    } else {
      $s = preg_replace_callback('/\<\?(.*?)\?\>/ims', array($this, 'callback_replace_php'), $s);
    }
    
    while ($s) {
      if (preg_match('/^(((\$template|\$custom)?\.?)?\w*+(\.\w\w*+)*)\s*=\s*(\[|\{|\()?/i', $s, $m)) {
          $tag = $m[1];
          $s = ltrim(substr($s, strlen($m[0])));
          if (isset($m[5])) {
            $i = self::find_close($s, $m[5]);
          } else {
            $i = strpos($s, "\n");
          }
          
          $value = trim(substr($s, 0, $i));
          $s = ltrim(substr($s, $i));

          $this->settag($tag, $value);
        } else {
          if ($i = strpos($s, "\n")) {
            $s = ltrim(substr($s, $i));
          } else {
            $s = '';
          }
        }
      }
      
    }
    
    public function settag($parent, $s) {
      if (isset($this->pathmap[$parent])) {
        $parent = $this->pathmap[$parent];
      }
      
      if (preg_match('/file\s*=\s*(\w[\w\._\-]*?\.\w\w*+\s*)/i', $s, $m) ||
      preg_match('/\@import\s*\(\s*(\w[\w\._\-]*?\.\w\w*+\s*)\)/i', $s, $m)) {
        $filename = litepublisher::$paths->themes . $this->theme->name . DIRECTORY_SEPARATOR . $m[1];
        if (!file_exists($filename)) $this->error("File '$filename' not found");
        $s = $this->getfile($filename, $this->getabout($this->theme->name));
      }

$parent = $this->preparetag($parent);

      if ($this->removephp) {
        $s = preg_replace('/\<\?.*?\?\>/ims', '', $s);
      } else {
        $s = preg_replace_callback('/\<\?(.*?)\?\>/ims', array($this, 'callback_replace_php'), $s);
      }
      
      while ($s && preg_match('/(\$\w*+(\.\w\w*+)?)\s*=\s*(\[|\{|\()?/i', $s, $m)) {
          if (!isset($m[3])) {
            $this->error('The bracket not found in ' . $s);
          }
          $tag = $m[1];
          $j = strpos($s, $m[0]);
          $pre  = rtrim(substr($s, 0, $j));
          $s= ltrim(substr($s, $j + strlen($m[0])));
          $i = self::find_close($s, $m[3]);
          $value = trim(substr($s, 0, $i));
          $s = ltrim(substr($s, $i + 1));
          
          $checkpath = $parent . '.' . substr($tag, 1);
          if (isset($this->pathmap[$checkpath])) {
            $newpath = $this->pathmap[$checkpath];
            $info = $this->paths[$newpath];
            $info['path'] = $newpath;
          } else {
            $info = $this->getinfo($parent, $tag);
          }
          
          $this->settag($info['path'], $value);
          $s = $pre . $info['replace'] . $s;
        }
        
        $s = trim($s);
        if (!$this->removephp) {
          $s = preg_replace_callback('/\<\&\#63;.*?\&\#63;\>/ims', array($this, 'callback_restore_php'), $s);
        }
        
$this->setvalue($parent, $s);
      }

protected function preparetag($name) {
      if (strbegin($name, '$template.')) $name = substr($name, strlen('$template.'));
return$name;
}

protected function setvalue($name, $value) {
        if (isset($this->paths[$name])) {
$this->theme->tempates[$name] = $value;
        } else {
          $this->error("The '$name' tag not found. Content \n$s");
        }
}

      public function getinfo($name, $child) {
$path = $name . '.' . substr($child, 1);
if (isset($this->paths[$path])) {
$info = $this->paths[$path];
$info['path'] = $path;
return $info;
} else {
/*
        foreach ($this->paths as $path => $info) {
          if (strbegin($path, $name) && ($child == $info['tag'])) {
              $info['path'] = $path;
              return $info;
            }
          }
*/
}

        $this->error("The '$child' not found in path '$name'");
}
      
      public function afterparse($theme) {
        $this->onfix($theme);
$this->reuse($this->theme->templates);
        }

public function reuse(&$templates) {
        foreach ($templates as $k => $v) {
          if (is_string($v) && !strbegin($v, '<') && isset($templates[$v]) && is_string($templates[$v])) {
            $templates[$k] = $templates[$v];
          }
        }
}        

            public function loadpaths() {
        $result = array();
        foreach ($this->tagfiles as $filename) {
          $filename = litepublisher::$paths->home . trim($filename, '/');
          if ($filename && file_exists($filename) && ($a = parse_ini_file($filename, true))) {
            if (isset($a['remap'])) {
              $this->pathmap = $this->pathmap + $a['remap'];
              unset($a['remap']);
            }
            $result = $result + $a;
          }
        }
        
        $result = $result + $this->extrapaths;
        $this->callevent('ongetpaths', array(&$result));
        return $result;
      }
      
    }//class