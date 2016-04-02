<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class basetheme extends tevents {
  public static $instances = array();
  public static $vars = array();
  public static $defaultargs;
  public $name;
  public $parsing;
  public $templates;
  public $extratml;

  public static function exists($name) {
    return file_exists(litepubl::$paths->themes . $name . '/about.ini');
  }

  public static function getbyname($classname, $name) {
    if (isset(static::$instances[$name])) {
      return static::$instances[$name];
    }

    $result = getinstance($classname);
    if ($result->name) {
      $result = litepubl::$classes->newinstance($classname);
    }

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

    if (!isset(static::$defaultargs)) static::set_defaultargs();
    $this->extratml = '';
  }

  public static function set_defaultargs() {
    static::$defaultargs = array(
      '$site.url' => litepubl::$site->url,
      '$site.files' => litepubl::$site->files,
      '{$site.q}' => litepubl::$site->q,
      '$site.q' => litepubl::$site->q
    );
  }

  public function __destruct() {
    unset(static::$instances[$this->name], $this->templates);
    parent::__destruct();
  }

  public function getbasename() {
    return 'themes/' . $this->name;
  }

  public function getparser() {
    return baseparser::i();
  }

  public function load() {
    if (!$this->name) return false;

    if (parent::load()) {
      static::$instances[$this->name] = $this;
      return true;
    }

    return $this->parsetheme();
  }

  public function parsetheme() {
    if (!static::exists($this->name)) {
      $this->error(sprintf('The %s theme not exists', $this->name));
    }

    $parser = $this->getparser();
    if ($parser->parse($this)) {
      static::$instances[$this->name] = $this;
    } else {
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
        return litepubl::$site;

      case 'lang':
        return tlocal::i();

      case 'post':
        $context = isset(litepubl::$urlmap->context) ? litepubl::$urlmap->context : ttemplate::i()->context;
        if ($context instanceof tpost) {
          return $context;
        }
        break;


      case 'author':
        return static::get_author();

      case 'metapost':
        return isset(static::$vars['post']) ? static::$vars['post']->meta : new emptyclass();
    } //switch
    if (isset($GLOBALS[$name])) {
      $var = $GLOBALS[$name];
    } else {
      $classes = litepubl::$classes;
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
      litepubl::$options->trace(sprintf('Object "%s" not found in %s', $name, $this->parsing[count($this->parsing) - 1]));
      return false;
    }

    return $var;
  }

  public function parsecallback($names) {
    $name = $names[1];
    $prop = $names[2];
    if (isset(static::$vars[$name])) {
      $var = static::$vars[$name];
    } elseif ($name == 'custom') {
      return $this->parse($this->templates['custom'][$prop]);
    } elseif ($name == 'label') {
      return "\$$name.$prop";
    } elseif ($var = $this->getvar($name)) {
      static::$vars[$name] = $var;
    } elseif (($name == 'metapost') && isset(static::$vars['post'])) {
      $var = static::$vars['post']->meta;
    } else {
      return '';
    }

    try {
      return $var->{$prop};
    }
    catch(Exception $e) {
      litepubl::$options->handexception($e);
    }
    return '';
  }

  public function parse($s) {
    if (!$s) return '';
    $s = strtr((string)$s, static::$defaultargs);
    if (isset($this->templates['content.admin.tableclass'])) $s = str_replace('$tableclass', $this->templates['content.admin.tableclass'], $s);
    array_push($this->parsing, $s);
    try {
      $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', $s);
      $result = preg_replace_callback('/\$([a-zA-Z]\w*+)\.(\w\w*+)/', array(
        $this,
        'parsecallback'
      ) , $s);
    }
    catch(Exception $e) {
      $result = '';
      litepubl::$options->handexception($e);
    }
    array_pop($this->parsing);
    return $result;
  }

  public function parsearg($s, targs $args) {
    $s = $this->parse($s);
    $s = $args->callback($s);
    return strtr($s, $args->data);
  }

  public function replacelang($s, $lang) {
    $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', (string)$s);
    static::$vars['lang'] = isset($lang) ? $lang : tlocal::i('default');
    $s = strtr($s, static::$defaultargs);
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
    static::$vars[$name] = $var;
    return static::i()->parse($s);
  }

  public static function clearcache() {
    tfiler::delete(litepubl::$paths->data . 'themes', false, false);
    litepubl::$urlmap->clearcache();
  }

  public function h($s) {
    return sprintf('<h4>%s</h4>', $s);
  }

  public function link($url, $title) {
    return sprintf('<a href="%s%s">%s</a>', strbegin($url, 'http') ? '' : litepubl::$site->url, $url, $title);
  }

  public static function quote($s) {
    return strtr($s, array(
      '"' => '&quot;',
      "'" => '&#039;',
      '\\' => '&#092;',
      '$' => '&#36;',
      '%' => '&#37;',
      '_' => '&#95;'
    ));
  }

} //class