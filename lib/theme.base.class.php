<?php

class basetheme extends tevents {
  public static $instances = array();
  public static $vars = array();
  public static $defaultargs;
  public $name;
  public $parsing;
  public $templates;
  public $extratml;
  
  public static function exists($name) {
    return file_exists(litepublisher::$paths->themes . $name . '/about.ini');
  }

  public static function getbyname($classname, $name) {  
    if (isset(self::$instances[$name])) {
return self::$instances[$name];
}

    $result = getinstance($classname);
    if ($result->name != '') $result = litepublisher::$classes->newinstance($classname);
    $result->name = $name;
    $result->load();
    return $result;
  }
  
  protected function create() {
    parent::create();
    $this->name = '';
    $this->parsing = array();
    $this->data['type'] = 'litepublisher';
    $this->data['parent'] = '';
    $this->addmap('templates', array());
    $this->templates = array();

    if (!isset(self::$defaultargs)) self::set_defaultargs();
    $this->extratml = '';
  }
  
  public static function set_defaultargs() {
    self::$defaultargs = array(
    '$site.url' => litepublisher::$site->url,
    '$site.files' => litepublisher::$site->files,
  '{$site.q}' => litepublisher::$site->q,
    '$site.q' => litepublisher::$site->q
    );
  }
  
  public function __destruct() {
    unset(self::$instances[$this->name], $this->templates);
    parent::__destruct();
  }
  
  public function getbasename() {
    return 'themes/' . $this->name;
  }
  
  public function load() {
    if ($this->name == '') return false;

    if (parent::load()) {
      self::$instances[$this->name] = $this;
      return true;
    }

    return $this->parsetheme();
  }
  
  public function parsetheme() {
    if (!self::exists($this->name)) {
      $this->error(sprintf('The %s theme not exists', $this->name));
    }
    
    $parser = tthemeparser::i();
    if ($parser->parse($this)) {
      self::$instances[$this->name] = $this;
      $this->save();
    }else {
      $this->error(sprintf('Theme file %s not exists', $filename));
    }
  }
  
  
  public function __set($name, $value) {
    if (array_key_exists($name, $this->templates)) {
      $this->templates[$name] = $value;
      return;
    }
    return parent::__set($name, $value);
  }

  public function reg($exp) {
    if (!strpos($exp, '\.')) $exp = str_replace('.', '\.', $exp);
    $result = array();
    foreach ($this->templates as $name => $val) {
      if (preg_match($exp, $name)) $result[$name] = $val;
    }
    return $result;
  }
  
  protected function getvar($name) {
    switch ($name) {
      case 'site':
      return litepublisher::$site;
      
      case 'lang':
      return tlocal::i();
      
      case 'post':
      $context = isset(litepublisher::$urlmap->context) ? litepublisher::$urlmap->context : ttemplate::i()->context;
      if ($context instanceof tpost) return $context;
      break;
      
      case 'author':
      return self::get_author();
      
      case 'metapost':
      return isset(self::$vars['post']) ? self::$vars['post']->meta : new emptyclass();
    } //switch
    
    if (isset($GLOBALS[$name])) {
      $var =  $GLOBALS[$name];
    } else {
      $classes = litepublisher::$classes;
      $var = $classes->gettemplatevar($name);
      if (!$var) {
        if (isset($classes->classes[$name])) {
          $var = $classes->getinstance($classes->classes[$name]);
        } elseif (isset($classes->items[$name])) {
          $var = $classes->getinstance($name);
        } else {
          $class = 't' . $name;
          if (isset($classes->items[$class])) $var = $classes->getinstance($class);
        }
      }
    }
    
    if (!is_object($var)) {
      litepublisher::$options->trace(sprintf('Object "%s" not found in %s', $name, $this->parsing[count($this->parsing) -1]));
      return false;
    }
    
    return $var;
  }
  
  public function parsecallback($names) {
    $name = $names[1];
    $prop = $names[2];
    if (isset(self::$vars[$name])) {
      $var =  self::$vars[$name];
    } elseif ($name == 'custom') {
      return $this->parse($this->templates['custom'][$prop]);
    } elseif ($name == 'label') {
      return "\$$name.$prop";
    } elseif ($var = $this->getvar($name)) {
      self::$vars[$name] = $var;
    } elseif (($name == 'metapost') && isset(self::$vars['post'])) {
      $var = self::$vars['post']->meta;
    } else {
      return '';
    }
    
    try {
    return $var->{$prop};
    } catch (Exception $e) {
      litepublisher::$options->handexception($e);
    }
    return '';
  }
  
  public function parse($s) {
    if (!$s) return '';
    $s = strtr((string) $s, self::$defaultargs);
    if (isset($this->templates['content.admin.tableclass'])) $s = str_replace('$tableclass', $this->templates['content.admin.tableclass'], $s);
    array_push($this->parsing, $s);
    try {
      $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', $s);
      $result = preg_replace_callback('/\$([a-zA-Z]\w*+)\.(\w\w*+)/', array($this, 'parsecallback'), $s);
    } catch (Exception $e) {
      $result = '';
      litepublisher::$options->handexception($e);
    }
    array_pop($this->parsing);
    return $result;
  }
  
  public function parsearg($s, targs $args) {
    $s = $this->parse($s);
    $s = $args->callback($s);
    return strtr ($s, $args->data);
  }
  
  public function replacelang($s, $lang) {
    $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', (string) $s);
    self::$vars['lang'] = isset($lang) ? $lang : tlocal::i('default');
    $s = strtr($s, self::$defaultargs);
    if (preg_match_all('/\$lang\.(\w\w*+)/', $s, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $name = $item[1];
      if ($v = $lang->{$name}) {
          $s = str_replace($item[0], $v, $s);
        }
      }
    }
    return $s;
  }
  
  public static function parsevar($name, $var, $s) {
    self::$vars[$name] = $var;
    return self::i()->parse($s);
  }

  public static function clearcache() {
    tfiler::delete(litepublisher::$paths->data . 'themes', false, false);
    litepublisher::$urlmap->clearcache();
  }
  
  public static function cacheini($filename) {
    if (isset(self::$inifiles[$filename])) return self::$inifiles[$filename];
    $datafile = tlocal::getcachedir() . sprintf('cacheini.%s.php', md5($filename));
    if (!tfilestorage::loadvar($datafile, $ini) || !is_array($ini)) {
      if (file_exists($filename)) {
        $ini = parse_ini_file($filename, true);
        tfilestorage::savevar($datafile, $ini);
      } else {
        $ini = array();
      }
    }
    
    if (!isset(self::$inifiles)) self::$inifiles = array();
    self::$inifiles[$filename] = $ini;
    return $ini;
  }
  
  public static function inifile($class, $filename) {
    $dir = litepublisher::$classes->getresourcedir($class);
    return self::cacheini($dir . $filename);
  }

}//class