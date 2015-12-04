<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tthemeparser extends tevents {
  public $theme;
  public $tagfiles;
  public $paths;
  public $extrapaths;
  public $fixsubcount;
  private $abouts;
  private $sidebar_index;
  private $pathmap;
  private $parsedtags;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'themeparser';
    $this->addevents('ongetpaths', 'beforeparse', 'parsed', 'onfix');
    $this->addmap('tagfiles', array('lib/install/ini/themeparser.ini'));
    $this->addmap('extrapaths', array());
    $this->data['replacelang'] = false;
    $this->data['removephp'] = true;
    
    $this->fixsubcount = true;
    
    $this->sidebar_index = 0;
    $this->pathmap = $this->createpathmap();
  }
  
  private function createpathmap() {
    $result = array();
    $post = 'content.post.filelist.';
    $excerpt = 'content.excerpts.excerpt.filelist.';
    foreach(array('file', 'image',  'audio', 'video', 'flash') as $name) {
      $key = $post . $name;
      $result[$key . "s.$name"] = $key;
      $keyexcerpt = $excerpt . $name;
      $result[$keyexcerpt . "s.$name"] = $keyexcerpt;
    }
    
    $result[$post . 'images.preview'] = $post . 'preview';
    $result[$excerpt . 'images.preview'] = $excerpt . 'preview';
    
    return $result;
  }
  
  public function parse(ttheme $theme) {
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
  
  public function doreplacelang($theme) {
    $lang = tlocal::i('comment');
    foreach ($theme->templates as $name => $value) {
      if (is_string($value)) {
        $theme->templates[$name] = $theme->replacelang($value, $lang);
      }
    }
    
    foreach ($theme->templates['sidebars'] as &$sidebar) {
      unset($widget);
      foreach ($sidebar as &$widget) {
        $widget = $theme->replacelang($widget, $lang);
      }
    }
    
  }
  
  public function callback_replace_php($m) {
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
  
  public function parsetheme(ttheme $theme) {
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
    
    //fix some old tags
    $s = strtr($s, array(
    '$options.url$url' => '$link',
    '$post.categorieslinks' => '$post.catlinks',
    '$post.tagslinks' => '$post.taglinks',
    '$post.subscriberss' => '$post.rsslink',
    '$post.excerptcategories' => '$post.excerptcatlinks',
    '$post.excerpttags' => '$post.excerpttaglinks',
    '$options' => '$site',
    '$template.sitebar' => '$template.sidebar',
    '<!--sitebar-->' => '<!--sidebar-->',
    '<!--/sitebar-->' => '<!--/sidebar-->'
    ));
    
    //replace $about.*
    if (preg_match_all('/\$about\.(\w\w*+)/', $s, $m, PREG_SET_ORDER)) {
      $a = array();
      foreach ($m as $item) {
        $name = $item[1];
        if (isset($about[$name])) {
          $a[$item[0]] = $about[$name];
          //$s = str_replace($item[0], $about[$name], $s);
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
  
  public function parsetags(ttheme $theme, $s) {
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
    
    while ($s != '') {
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
      if (preg_match('/file\s*=\s*(\w[\w\._\-]*?\.\w\w*+\s*)/i', $s, $m) ||
      preg_match('/\@import\s*\(\s*(\w[\w\._\-]*?\.\w\w*+\s*)\)/i', $s, $m)) {
        $filename = litepublisher::$paths->themes . $this->theme->name . DIRECTORY_SEPARATOR . $m[1];
        if (!file_exists($filename)) $this->error("File '$filename' not found");
        $s = $this->getfile($filename, $this->getabout($this->theme->name));
      }
      
      if (strbegin($parent, '$template.')) $parent = substr($parent, strlen('$template.'));
      if (strbegin($parent, 'sidebar')) {
        if (preg_match('/^sidebar(\d)\.?/', $parent, $m)) {
          $this->sidebar_index = (int) $m[1];
        } else {
          $this->sidebar_index = 0;
        }
        
        if (!isset($this->theme->templates['sidebars'][$this->sidebar_index])) {
          $this->theme->templates['sidebars'][$this->sidebar_index] = array();
        }
      }
      
      if ($this->removephp) {
        //if (preg_match('/\<\?.*?\?\>/ims', $s, $m)) dumpvar($m);
        $s = preg_replace('/\<\?.*?\?\>/ims', '', $s);
      } else {
        $s = preg_replace_callback('/\<\?(.*?)\?\>/ims', array($this, 'callback_replace_php'), $s);
      }
      
      while (($s != '') && preg_match('/(\$\w*+(\.\w\w*+)?)\s*=\s*(\[|\{|\()?/i', $s, $m)) {
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
          
          $info = $this->tagtopath($parent, $tag);
          $this->settag($info['path'], $value);
          $s = $pre . $info['replace'] . $s;
        }
        
        $s = trim($s);
        if (!$this->removephp) {
          $s = preg_replace_callback('/\<\&\#63;.*?\&\#63;\>/ims', array($this, 'callback_restore_php'), $s);
        }
        
        //retranslatepaths
        if (isset($this->pathmap[$parent])) $parent = $this->pathmap[$parent];
        
        //set value
        if (strbegin($parent, 'sidebar')) {
          $this->setwidgetvalue($parent, $s);
        }  elseif (isset($this->paths[$parent])) {
          $this->set_value($parent, $s);
        } elseif (($parent == '') || ($parent == '$template')) {
          $this->theme->templates['index'] = $s;
          //dumpstr($s);
        } elseif (strbegin($parent, '$custom') || strbegin($parent, 'custom')) {
          $this->setcustom($parent, $s);
        } else {
          $this->error("The '$parent' tag not found. Content \n$s");
        }
      }
      
      public function set_value($name, $value) {
        $this->parsedtags[] = $name;
        switch ($name) {
          case 'content.menu':
          //fix old ver
          $this->theme->templates['content.author'] = str_replace('menu', 'author', $value);
          break;
          
          case 'menu.item':
          $this->theme->templates['menu.single'] = $value;
          $this->theme->templates['menu.current'] = $value;
          break;
        }
        
        $this->theme->templates[$name] = $value;
      }
      
      public function tagtopath($parent, $tag) {
        if (strbegin($tag,  '$template.sidebar') && (substr_count($tag, '.') == 1)) {
          return array(
          'path' => substr($tag, strlen('$template.')),
          'tag' => $tag,
          'replace' => $tag
          );
        }
        
        if (($parent == '') || ($tag == '$template')) return 'index';
        if (strbegin($parent, '$template.')) $parent = substr($parent, strlen('$template.'));
        if ($parent == '$template') $parent = '';
        foreach ($this->paths as $path => $info) {
          if (strbegin($path, $parent)) {
            if ($tag == $info['tag']) {
              $info['path'] = $path;
              return $info;
            }
          }
        }
        
        $name = substr($tag, 1);
        $path = $parent . '.' . $name;
        if (strbegin($parent, 'sidebar')) {
          return array(
          'path' => $path,
          'tag' => $tag,
          'replace' => $tag == '$classes' ? '' : $tag
          );
        }
        
        if (strbegin($parent, '$custom') || strbegin($parent, 'custom')) {
          return array(
          'path' => $path,
          'tag' => $tag,
          'replace' => ''
          );
        }
        
        $this->error("The '$tag' not found in path '$parent'");
      }
      
      private function setwidgetvalue($path, $value) {
        if (!strpos($path, '.')) return;
        if (!preg_match('/^sidebar(\d?)\.(\w\w*+)(\.\w\w*+)*$/', $path, $m)) $this->error("The '$path' is not a widget path");
        $widgetname = $m[2];
        //backward compability deprecated submenu
        if ($widgetname == 'submenu') return;
        
        if (($widgetname != 'widget') && (!in_array($widgetname, ttheme::getwidgetnames()))) $this->error("Unknown widget '$widgetname' name");
        
        $path = ttheme::getwidgetpath(empty($m[3]) ? '' : $m[3]);
        if ($path === false) $this->error("Unknown '$path' widget path");
        
        $this->setwidgetitem($widgetname, $path, $value);
        
        if ($widgetname == 'widget') {
          foreach (ttheme::getwidgetnames() as $widgetname) {
            if ((($widgetname == 'posts') || ($widgetname == 'comments')) &&
            ($path =='.item')) continue;
            
            $this->setwidgetitem($widgetname, $path, $value);
          }
        }
      }
      
      private function setwidgetitem($widgetname, $path, $value) {
        $sidebar = &$this->theme->templates['sidebars'][$this->sidebar_index];
        if (!isset($sidebar[$widgetname])) {
          foreach ( array('', '.items', '.item', '.subcount', '.subitems') as $name) {
            $sidebar[$widgetname . $name] = isset($sidebar['widget' . $name]) ? $sidebar['widget' . $name] : '';
          }
          if ($widgetname == 'meta') $sidebar['meta.classes'] = '';
        }
        
        $sidebar[$widgetname . $path] = $value;
      }
      
      public function setcustom($path, $value) {
        $names = explode('.', $path);
        if (count($names) < 2) return;
        if (($names[0] != '$custom') && ($names[0] != 'custom')) $this->error("The '$path' path is not a custom path");
        $name = $names[1];
        switch (count($names)) {
          case 2:
          $this->theme->templates['custom'][$name] = $value;
          return;
          
          case 3:
          return;
          
          case 4:
          $tag = $names[3];
          $admin = &$this->theme->templates['customadmin'];
          if (!isset($admin[$name])) $admin[$name] = array();
          if ($tag == 'values') {
            $value = explode(',', $value);
            foreach ($value as $i => $v) $value[$i] = trim($v);
          }
          
          $admin[$name][$tag] = $value;
          return;
        }
      }
      
      public function afterparse($theme) {
        $this->onfix($theme);
        
        $templates = &$this->theme->templates;
        $templates['menu.hover'] = isset($templates['menu.hover']) ? ($templates['menu.hover'] == 'true' ? 'true' :
        ($templates['menu.hover'] == 'bootstrap' ? 'bootstrap' : 'false')) : 'true';
        
        if (!isset($templates['content.post.templatecomments'])) $templates['content.post.templatecomments'] = '';
        if (!isset($templates['content.post.templatecomments.confirmform'])) {
          echo implode('<br>', array_keys($templates));
          $this->error('template "content.post.templatecomments.confirmform" not exists');
        }
        
        $post = 'content.post.';
        $excerpt = 'content.excerpts.excerpt.';
        //normalize filelist
        foreach(array('file', 'image',  'audio', 'video', 'flash') as $name) {
          $key = $post . 'filelist.' . $name;
          $itemkey = $key . "s.$name";
          if (!isset($templates[$key . 's'])) $templates[$key . 's'] = '$' . $name;
          //excerpt
          $keyexcerpt = $excerpt . 'filelist.' . $name;
          $itemkeyexcerpt = $keyexcerpt . "s.$name";
          if (!isset($templates[$keyexcerpt])) $templates[$keyexcerpt] = $templates[$key];
          if (!isset($templates[$keyexcerpt . 's'])) $templates[$keyexcerpt . 's'] = $templates[$key . 's'];
        }
        
        //fix preview
        $key = $post . 'filelist.preview';
        $keyexcerpt = $excerpt . 'filelist.preview';
        if ( !isset($templates[$keyexcerpt])) $templates[$keyexcerpt] = $templates[$key];
        
        foreach (array('date',
        'filelist', 'filelist.file', 'filelist.image', 'filelist.preview', 'filelist.audio', 'filelist.video', 'filelist.flash',
        'filelist.files', 'filelist.images', 'filelist.audios', 'filelist.videos', 'filelist.flashs',
        'catlinks',         'catlinks.item', 'catlinks.divider',
        'taglinks',         'taglinks.item', 'taglinks.divider') as $name) {
          if (empty($templates[$excerpt . $name])) {
            $templates[$excerpt . $name] = $templates[$post . $name];
          }
        }
        
        $sidebars = &$templates['sidebars'];
        for ($i = 0; $i < count($sidebars); $i++) {
          $sidebar = &$sidebars[$i];
          foreach (ttheme::getwidgetnames() as $widgetname) {
            foreach (array('', '.items', '.item', '.subcount', '.subitems') as $name) {
              if (empty($sidebar[$widgetname . $name])) $sidebar[$widgetname . $name] = $sidebar['widget' . $name];
            }
            
            if ($this->fixsubcount && in_array($widgetname, array('widget', 'categories', 'tags', 'archives'))) {
              $v = $sidebar[$widgetname . '.item'];
              if (!strpos($v, '$subcount')) $sidebar[$widgetname . '.item'] = str_replace('$subitems', '$subcount$subitems', $v);
            }
            
          }
          
          if (is_string($sidebar['meta.classes'])) {
            $sidebar['meta.classes'] = self::getmetaclasses($sidebar['meta.classes']);
          }
        }
        
        //add spaces
        foreach (array(
        'content.excerpts.excerpt.taglinks.divider',
        'content.post.taglinks.divider',
        'content.excerpts.excerpt.catlinks.divider',
        'content.post.catlinks.divider'
        ) as $k) {
          if (substr($templates[$k], -1) != ' ') $templates[$k] .= ' ';
        }
        
        $templates['content.post.templatecomments.confirmform'] = str_replace('$lang.formhead', '$lang.checkspam', $templates['content.post.templatecomments.confirmform']);
        
        $form = 'content.post.templatecomments.form';
        $templates[$form] = trim(str_replace(
        '<script type="text/javascript" src="$site.files$template.jsmerger_comments"></script>', '',
        $templates[$form]));
        if (!strpos($templates[$form], '$mesg')) $templates[$form] = '<div id="before-commentform">$mesg</div>' . $templates[$form];
        
        $regform = 'content.post.templatecomments.regform';
        if (!in_array($regform, $this->parsedtags) && in_array('content.admin.editor', $this->parsedtags)) {
          $editor = strtr($templates['content.admin.editor'], array(
          '$lang.$name' => $this->replacelang ? tlocal::i('comment')->content : '$lang.content',
          '$name' => 'content',
          '$value' => ''
          ));
          
          $templates[$regform] =
          '								<div id="before-commentform">$mesg</div>
          <h4 id="respond">$lang.leavereply</h4>
          <form action="$site.url/send-comment.php" method="post" id="commentform">'
          . $editor .
          '<p>
          <input type="hidden" name="postid" value="$postid" />
          <input type="hidden" name="antispam" value="$antispam" />
          
          <input type="submit" name="submitbutton" id="submitcomment" value="'
          . ($this->replacelang ? tlocal::i()->send : '$lang.send' ) .
          '" /></p>
          </form>';
        }
        
        $comment = 'content.post.templatecomments.comments.comment';
        $templates[$comment] = str_replace('$moderate',
        '<div class="moderationbuttons" data-idcomment="$comment.id" data-idauthor="$comment.author"></div>',
        $templates[$comment]);
        
        /*
        $css = '<link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />';
        if ($this->stylebefore) {
          foreach (array('index', 'index.home', 'index.post', 'index.tag') as $k) {
            if (!strpos($templates[$k], '$template.cssmerger_default')) continue;
            
            //insert css merger before theme css
            if ($i = strpos($templates[$k], '.css')) {
              $i = strrpos(substr($templates[$k], 0, $i), '<');
              
              $templates[$k] = substr_replace($templates[$k], $css, $i - 1, 0);
            }
          }
          
          //fix $template.head
          // ignore on installling (class not exists)
          if (!defined('litepublisher_mode')|| (litepublisher_mode != 'install')) {
            $t = ttemplate::i();
            if ((false !== strpos($t->heads, $css)) && (false === strpos($t->heads, "<!--$css-->"))) {
              $t->heads = str_replace($css, "<!--$css-->", $t->heads);
              $t->save();
            }
          }
        }
        */
        
        
        //reuse templates
        foreach ($templates as $k => $v) {
          if (is_string($v) && !strbegin($v, '<') && isset($templates[$v]) && is_string($templates[$v])) {
            $templates[$k] = $templates[$v];
          }
        }
        
      }//method
      
      public static function getmetaclasses($s) {
        $result = array('rss' => '', 'comments' => '', 'media' => '', 'foaf' => '', 'profile' => '', 'sitemap' => '');
        foreach (explode(',', $s) as $class) {
          if ($i = strpos($class, '=')) {
            $classname = trim(substr($class, 0, $i));
            $value = trim(substr($class, $i + 1));
            if ($value != '') $result[$classname] = sprintf('class="%s"', $value);
          }
        }
        return $result;
      }
      
      public static function compress(ttheme $theme, $dir = '') {
        if ($theme->name == 'default') return false;
        $result = '';
        if ($dir == '') $dir = litepublisher::$paths->themes . $theme->name . DIRECTORY_SEPARATOR;
        $parent = ttheme::getinstance($theme->parent == '' ? 'default' : $theme->parent);
        if ($theme->templates['index'] != $parent->templates['index']) {
          if (file_put_contents($dir . 'index.tml', $theme->templates['index']) === false) return false;
        $result .= '$template = {@import(index.tml)}';
          $result .= "\n\n";
        }
        
        foreach ($theme->templates as $name => $value) {
          if ($name == 'index') continue;
          if (is_array($value)) continue;
          $value = trim($value);
          if ($value == trim($parent->templates[$name])) continue;
          if (strend($name, '.date')) {
            if (($value == '') || ($value == litepublisher::$options->dateformat) || ($value == tlocal::get('datetime', 'dateformat'))) continue;
          }
          $result .= "\$template.$name = [$value]\n\n";
        }
        
        for ($i =0; $i < count($theme->templates['sidebars']); $i++ ) {
          $sidebar = &$theme->templates['sidebars'][$i];
          $parentsidebar =&$parent->templates['sidebars'][$i];
          foreach ($sidebar as $name => $value) {
            if (is_string($value)) {
              $value = trim($value);
              if ($value == trim($parentsidebar[$name])) continue;
            } else {
              if (count(array_diff_assoc($value, $parentsidebar[$name])) == 0) continue;
              $a = array_map(create_function('$k, $v', 'return "$k=$v";'),
              array_keys($value), array_values($value));
              $value = implode(',', $a);
            }
            
            $result .= $i == 0 ? 'sidebar.' : "sidebar$i.";
            $result .= "$name = [$value]\n\n";
          }
        }
        
        return file_put_contents($dir . 'theme.txt', $result);
      }
      
      public function loadpaths() {
        $result = array();
        foreach ($this->tagfiles as $filename) {
          $filename = litepublisher::$paths->home . trim($filename, '/');
          if ($filename && file_exists($filename) && ($a = parse_ini_file($filename, true))) {
            $result = $result + $a;
          }
        }
        
        $result = $result + $this->extrapaths;
        $this->callevent('ongetpaths', array(&$result));
        return $result;
      }
      
    }//class