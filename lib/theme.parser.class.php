<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tthemeparser extends tevents {
  public $theme;
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
    $this->addmap('extrapaths', array());
    $this->data['replacelang'] = false;
    $this->data['removephp'] = true;
    $this->data['stylebefore'] = true;
    
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
  
  public static function checktheme(ttheme $theme) {
    if ($about = self::get_about_wordpress_theme($theme->name)) {
      $theme->type = 'wordpress';
      return true;
    }
    return false;
  }
  
  public function parse(ttheme $theme) {
    $theme->lock();
    $this->checkparent($theme->name);
    $about = $this->getabout($theme->name);
    switch ($about['type']) {
      case 'litepublisher3':
      case 'litepublisher':
      $theme->type = 'litepublisher';
      $ver3 = tthemeparserver3::i();
      $ver3->parse($theme);
      break;
      
      case 'litepublisher4':
      $theme->type = 'litepublisher';
      $this->parsetheme($theme);
      break;
      
      case 'wordpress':
      $theme->type = 'wordpress';
      break;
    }
    
    /*
    //save and restore lang section
    $lang = tlocal::i();
    $sect = $lang->section;
    */
    
    $this->parsed($theme);
    if ($this->replacelang) $this->doreplacelang($theme);
    //$lang->section = $sect;
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
      $parentname = empty($about['parent']) ? 'default' : $about['parent'];
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
    //strip utf8 signature
    $utf = "\xEF\xBB\xBF";
    if (strbegin($s, $utf)) $s = substr($s, strlen($utf));
    $s = str_replace(array("\r\n", "\r", "\n\n"), "\n", $s);
    //strip coments
    $s = preg_replace('/\s*\/\*.*?\*\/\s*/sm', '', $s);
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
        /*
      } elseif ($about =  twordpressthemeparser::get_about_wordpress_theme($name)){
        $about['type'] = 'wordpress';
        $this->abouts[$name] = $about;
        */
      } else {
        $this->abouts[$name] = false;
      }
    }
    return $this->abouts[$name];
  }
  
  public function checkparent($name) {
    $about = $this->getabout($name);
    $parents = array($name);
    while (!empty($about['parent'])) {
      $name = $about['parent'];
      if (in_array($name, $parents)) {
        $this->error(sprintf('Theme cicle "%s"', implode(', ', $parents)));
      }
      
      $parents[] = $name;
      $about = $this->getabout($name);
    }
    
    return true;
  }
  
  public function changetheme($old, $name) {
    $template = ttemplate::i();
    if ($about = $this->getabout($old)) {
      if (!empty($about['about']['pluginclassname'])) {
        $plugins = tplugins::i();
        $plugins->delete($old);
      }
    }
    
    $template->data['theme'] = $name;
    $template->path = litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR  ;
    $template->url = litepublisher::$site->url  . '/themes/'. $template->theme;
    
    $theme = ttheme::getinstance($name);
    
    $about = $this->getabout($name);
    if (!empty($about['about']['pluginclassname'])) {
      $plugins = tplugins::i();
      $plugins->addext($name, $about['about']['pluginclassname'], $about['about']['pluginfilename']);
    }
    
    litepublisher::$urlmap->clearcache();
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
    $this->paths = $this->getpaths($theme);
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
        if (!isset($this->theme->templates['sidebars'][$this->sidebar_index])) $this->theme->templates['sidebars'][$this->sidebar_index] = array();
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
        //echo "$widgetname, $path<br>";
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
        if (!isset($templates['content.post.templatecomments.confirmform'])) $this->error('tml not');
        
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
        
        $sidebars = &$this->theme->templates['sidebars'];
        $count = substr_count($this->theme->templates['index'], '$template.sidebar');
        if (count($sidebars) > $count) array_splice($sidebars, $count , count($sidebars) - $count);
        for ($i = 0; $i < $count; $i++) {
          $sidebar = &$this->theme->templates['sidebars'][$i];
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
        
        if ($this->stylebefore) {
          foreach (array('index', 'index.home', 'index.post', 'index.tag') as $k) {
            if (strpos($templates[$k], '$template.cssmerger_default')) continue;
            //insert css merger before theme css
            if ($i = strpos($templates[$k], '.css')) {
              $i = strrpos(substr($templates[$k], 0, $i), '<');
              $css = '<link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />';
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
      
      public function getpaths() {
        $extra = $this->extrapaths;
        $this->callevent('ongetpaths', array(&$extra));
        return $extra + array(
        'index' => array(
        'tag' => '',
        'replace' => ''
        ),
        
        'index.home' => array(
        'tag' => '$template.index.home',
        'replace' => ''
        ),
        
        'index.post' => array(
        'tag' => '$template.index.post',
        'replace' => ''
        ),
        
        'index.tag' => array(
        'tag' => '$template.index.tag',
        'replace' => ''
        ),
        
        'title' => array(
        'tag' => '$template.title',
        'replace' => '$template.title'
        ),
        
        'menu' => array(
        'tag' => '$template.menu',
        'replace' => '$template.menu'
        ),
        
        'menu.hover' => array(
        'tag' => '$hover',
        'replace' => ''
        ),
        
        'menu.item' => array(
        'tag' => '$item',
        'replace' => '$item'
        ),
        
        'menu.current' => array(
        'tag' => '$current',
        'replace' => ''
        ),
        
        'menu.item.submenu' => array(
        'tag' => '$submenu',
        'replace' => '$submenu'
        ),
        
        'menu.single' => array(
        'tag' => '$single',
        'replace' => ''
        ),
        
        'head' => array(
        'tag' => '$template.head',
        'replace' => '$template.head'
        ),
        
        'head.post' => array(
        'tag' => '$post',
        'replace' => ''
        ),
        
        'head.post.prev' => array(
        'tag' => '$prev',
        'replace' => ''
        ),
        
        'head.post.next' => array(
        'tag' => '$next',
        'replace' => ''
        ),
        
        'head.post.rss' => array(
        'tag' => '$rss',
        'replace' => ''
        ),
        
        'head.tags' => array(
        'tag' => '$tags',
        'replace' => ''
        ),
        
        'content' => array(
        'tag' => '$template.content',
        'replace' => '$template.content'
        ),
        
        'content.simple' => array(
        'tag' => '$simple',
        'replace' => ''
        ),
        
        'content.notfound' => array(
        'tag' => '$notfound',
        'replace' => ''
        ),
        
        'content.menu' => array(
        'tag' => '$menu',
        'replace' => ''
        ),
        
        'content.author' => array(
        'tag' => '$author',
        'replace' => ''
        ),
        
        'content.home' => array(
        'tag' => '$home',
        'replace' => ''
        ),
        
        'content.home.midle' => array(
        'tag' => '$midle',
        'replace' => '$midle'
        ),
        
        'content.home.midle.post' => array(
        'tag' => '$post',
        'replace' => '$post'
        ),
        
        'content.post' => array(
        'tag' => '$post',
        'replace' => ''
        ),
        
        'content.post.more' => array(
        'tag' => '$post.more',
        'replace' => ''
        ),
        
        'content.post.rsslink' => array(
        'tag' => '$post.rsslink',
        'replace' => '$post.rsslink'
        ),
        
        'content.post.date' => array(
        'tag' => '$post.date',
        'replace' => '$post.date'
        ),
        
        'content.post.filelist' => array(
        'tag' => '$post.filelist',
        'replace' => '$post.filelist'
        ),
        
        'content.post.filelist.files' => array(
        'tag' => '$files',
        'replace' => '$files'
        ),
        
        'content.post.filelist.file' => array(
        'tag' => '$file',
        'replace' => '$files'
        ),
        
        'content.post.filelist.files.file' => array(
        'tag' => '$file',
        'replace' => '$file'
        ),
        
        'content.post.filelist.image' => array(
        'tag' => '$image',
        'replace' => ''
        ),
        
        'content.post.filelist.images' => array(
        'tag' => '$images',
        'replace' => ''
        ),
        
        'content.post.filelist.images.image' => array(
        'tag' => '$image',
        'replace' => '$image'
        ),
        
        'content.post.filelist.preview' => array(
        'tag' => '$preview',
        'replace' => ''
        ),
        
        'content.post.filelist.images.preview' => array(
        'tag' => '$preview',
        'replace' => ''
        ),
        
        'content.post.filelist.audio' => array(
        'tag' => '$audio',
        'replace' => ''
        ),
        
        'content.post.filelist.audios.audio' => array(
        'tag' => '$audio',
        'replace' => '$audio'
        ),
        
        'content.post.filelist.audios' => array(
        'tag' => '$audios',
        'replace' => ''
        ),
        
        'content.post.filelist.video' => array(
        'tag' => '$video',
        'replace' => ''
        ),
        
        'content.post.filelist.videos' => array(
        'tag' => '$videos',
        'replace' => ''
        ),
        
        'content.post.filelist.videos.video' => array(
        'tag' => '$video',
        'replace' => '$video'
        ),
        
        'content.post.filelist.flash' => array(
        'tag' => '$flash',
        'replace' => ''
        ),
        
        'content.post.filelist.flashs' => array(
        'tag' => '$flashs',
        'replace' => ''
        ),
        
        'content.post.filelist.flashs.flash' => array(
        'tag' => '$flash',
        'replace' => '$flash'
        ),
        
        'content.post.catlinks' => array(
        'tag' => '$post.catlinks',
        'replace' => '$post.catlinks'
        ),
        
        'content.post.catlinks.item' => array(
        'tag' => '$item',
        'replace' => '$items'
        ),
        
        'content.post.catlinks.divider' => array(
        'tag' => '$divider',
        'replace' => ''
        ),
        
        'content.post.taglinks' => array(
        'tag' => '$post.taglinks',
        'replace' => '$post.taglinks'
        ),
        
        'content.post.taglinks.item' => array(
        'tag' => '$item',
        'replace' => '$items'
        ),
        
        'content.post.taglinks.divider' => array(
        'tag' => '$divider',
        'replace' => ''
        ),
        
        'content.post.prevnext' => array(
        'tag' => '$post.prevnext',
        'replace' => '$post.prevnext'
        ),
        
        'content.post.prevnext.prev' => array(
        'tag' => '$prev',
        'replace' => '$prev'
        ),
        
        'content.post.prevnext.next' => array(
        'tag' => '$next',
        'replace' => '$next'
        ),
        
        'content.post.templatecomments' => array(
        'tag' => '$post.templatecomments',
        'replace' => '$post.templatecomments'
        ),
        
        'content.post.templatecomments.closed' => array(
        'tag' => '$closed',
        'replace' => ''
        ),
        
        'content.post.templatecomments.form' => array(
        'tag' => '$form',
        'replace' => ''
        ),
        
        'content.post.templatecomments.regform' => array(
        'tag' => '$regform',
        'replace' => ''
        ),
        
        'content.post.templatecomments.confirmform' => array(
        'tag' => '$confirmform',
        'replace' => ''
        ),
        
        'content.post.templatecomments.moderateform' => array(
        'tag' => '$moderateform',
        'replace' => ''
        ),
        
        'content.post.templatecomments.holdcomments' => array(
        'tag' => '$holdcomments',
        'replace' => ''
        ),
        
        'content.post.templatecomments.comments' => array(
        'tag' => '$comments',
        'replace' => ''
        ),
        
        'content.post.templatecomments.comments.id' => array(
        'tag' => '$id',
        'replace' => ''
        ),
        
        'content.post.templatecomments.comments.idhold' => array(
        'tag' => '$idhold',
        'replace' => ''
        ),
        
        'content.post.templatecomments.comments.count' => array(
        'tag' => '$count',
        'replace' => ''
        ),
        
        'content.post.templatecomments.comments.comment' => array(
        'tag' => '$comment',
        'replace' => '$comment'
        ),
        
        'content.post.templatecomments.comments.comment.class1' => array(
        'tag' => '$class1',
        'replace' => ' $class'
        ),
        
        'content.post.templatecomments.comments.comment.class2' => array(
        'tag' => '$class2',
        'replace' => ' '
        ),
        
        'content.post.templatecomments.comments.comment.date' => array(
        'tag' => '$comment.date',
        'replace' => '$comment.date'
        ),
        
        'content.post.templatecomments.comments.comment.moderate' => array(
        'tag' => '$moderate',
        'replace' => '$moderate'
        ),
        
        'content.post.templatecomments.comments.comment.quotebuttons' => array(
        'tag' => '$quotebuttons',
        'replace' => '$quotebuttons'
        ),
        
        'content.post.templatecomments.pingbacks' => array(
        'tag' => '$pingbacks',
        'replace' => ''
        ),
        
        'content.post.templatecomments.pingbacks.pingback' => array(
        'tag' => '$pingback',
        'replace' => '$pingback'
        ),
        
        'content.excerpts' => array(
        'tag' => '$excerpts',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt' => array(
        'tag' => '$excerpt',
        'replace' => '$excerpt'
        ),
        
        'content.excerpts.excerpt.firstimage' => array(
        'tag' => '$post.firstimage',
        'replace' => '$post.firstimage'
        ),
        
        'content.excerpts.excerpt.date' => array(
        'tag' => '$post.excerptdate',
        'replace' => '$post.excerptdate'
        ),
        
        'content.excerpts.excerpt.morelink' => array(
        'tag' => '$post.morelink',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.filelist' => array(
        'tag' => '$post.excerptfilelist',
        'replace' => '$post.excerptfilelist'
        ),
        
        'content.excerpts.excerpt.filelist.files' => array(
        'tag' => '$files',
        'replace' => '$files'
        ),
        
        'content.excerpts.excerpt.filelist.file' => array(
        'tag' => '$file',
        'replace' => '$files'
        ),
        
        'content.excerpts.excerpt.filelist.files.file' => array(
        'tag' => '$file',
        'replace' => '$file'
        ),
        
        'content.excerpts.excerpt.filelist.image' => array(
        'tag' => '$image',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.filelist.images' => array(
        'tag' => '$images',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.filelist.images.image' => array(
        'tag' => '$image',
        'replace' => '$image'
        ),
        
        'content.excerpts.excerpt.filelist.preview' => array(
        'tag' => '$preview',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.filelist.images.preview' => array(
        'tag' => '$preview',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.filelist.audio' => array(
        'tag' => '$audio',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.filelist.audios.audio' => array(
        'tag' => '$audio',
        'replace' => '$audio'
        ),
        
        'content.excerpts.excerpt.filelist.audios' => array(
        'tag' => '$audios',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.filelist.video' => array(
        'tag' => '$video',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.filelist.videos' => array(
        'tag' => '$videos',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.filelist.videos.video' => array(
        'tag' => '$video',
        'replace' => '$video'
        ),
        
        'content.excerpts.excerpt.filelist.flash' => array(
        'tag' => '$flash',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.filelist.flashs' => array(
        'tag' => '$flashs',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.filelist.flashs.flash' => array(
        'tag' => '$flash',
        'replace' => '$flash'
        ),
        
        'content.excerpts.excerpt.catlinks' => array(
        'tag' => '$post.excerptcatlinks',
        'replace' => '$post.excerptcatlinks'
        ),
        
        'content.excerpts.excerpt.catlinks.item' => array(
        'tag' => '$item',
        'replace' => '$items'
        ),
        
        'content.excerpts.excerpt.catlinks.divider' => array(
        'tag' => '$divider',
        'replace' => ''
        ),
        
        'content.excerpts.excerpt.taglinks' => array(
        'tag' => '$post.taglinks',
        'replace' => '$post.taglinks'
        ),
        
        'content.excerpts.excerpt.taglinks.item' => array(
        'tag' => '$item',
        'replace' => '$items'
        ),
        
        'content.excerpts.excerpt.taglinks.divider' => array(
        'tag' => '$divider',
        'replace' => ''
        ),
        
        'content.excerpts.lite' => array(
        'tag' => '$lite',
        'replace' => ''
        ),
        
        'content.excerpts.lite.excerpt' => array(
        'tag' => '$excerpt',
        'replace' => '$excerpt'
        ),
        
        'content.navi' => array(
        'tag' => '$navi',
        'replace' => ''
        ),
        
        'content.navi.prev' => array(
        'tag' => '$prev',
        'replace' => '$items'
        ),
        
        'content.navi.next' => array(
        'tag' => '$next',
        'replace' => ''
        ),
        
        'content.navi.link' => array(
        'tag' => '$link',
        'replace' => ''
        ),
        
        'content.navi.current' => array(
        'tag' => '$current',
        'replace' => ''
        ),
        
        'content.navi.divider' => array(
        'tag' => '$divider',
        'replace' => ''
        ),
        
        'content.admin' => array(
        'tag' => '$admin',
        'replace' => ''
        ),
        
        'content.admin.editor' => array(
        'tag' => '$editor',
        'replace' => ''
        ),
        
        'content.admin.text' => array(
        'tag' => '$text',
        'replace' => ''
        ),
        
        'content.admin.password' => array(
        'tag' => '$password',
        'replace' => ''
        ),
        
        'content.admin.upload' => array(
        'tag' => '$upload',
        'replace' => ''
        ),
        
        'content.admin.combo' => array(
        'tag' => '$combo',
        'replace' => ''
        ),
        
        'content.admin.checkbox' => array(
        'tag' => '$checkbox',
        'replace' => ''
        ),
        
        'content.admin.radioitem' => array(
        'tag' => '$radioitem',
        'replace' => ''
        ),
        
        'content.admin.radio' => array(
        'tag' => '$radio',
        'replace' => ''
        ),
        
        'content.admin.hidden' => array(
        'tag' => '$hidden',
        'replace' => ''
        ),
        
        'content.admin.button' => array(
        'tag' => '$button',
        'replace' => ''
        ),
        
        'content.admin.submit' => array(
        'tag' => '$submit',
        'replace' => ''
        ),
        
        'content.admin.form' => array(
        'tag' => '$form',
        'replace' => ''
        ),
        
        'content.admin.tableclass' => array(
        'tag' => '$tableclass',
        'replace' => ''
        ),
        
        'custom' => array(
        'tag' => '$custom',
        'replace' => ''
        ),
        
        'ajaxwidget' => array(
        'tag' => '$template.ajaxwidget',
        'replace' => '',
        ),
        
        'inlinewidget' => array(
        'tag' => '$template.inlinewidget',
        'replace' => ''
        )
        
        );
      }
      
    }//class