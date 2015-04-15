<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tthemeparserver3 extends tevents {
  public $theme;
  private $default;
  private $fixold;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'themeparserver3';
    $this->fixold = true;
  }
  
  public function parse(ttheme $theme) {
    $filename = litepublisher::$paths->themes . $theme->name . DIRECTORY_SEPARATOR . 'index.tml';
    if (!file_exists($filename))  return false;
    if ($theme->name == 'default') $this->error('Default theme must be in new format');
    $parser = tthemeparser::i();
    $about = $parser->getabout($theme->name);
    if (empty($about['parent'])) {
      $this->default = ttheme::getinstance('default');
    } else {
      $this->default = ttheme::getinstance($about['parent']);
      $theme->parent = $about['parent'];
    }
    
    $s = $parser->getfile($filename, $about);
    $this->theme = $theme;
    $theme->templates = $this->default->templates;
    $theme->title = $this->parsetitle($s);
    $this->parsemenu($s);
    $this->parsecontent($s);
    $theme->templates['sidebars'] = $this->parsesidebars($s);
    $s = $this->fixhead($s);
    $s = $this->deletespaces($s);
    $theme->templates['index'] = $s != ''? $s :  $this->default->templates['index'];
    return true;
  }
  
  public function parsetag(&$s, $tag, $replace) {
    $result = '';
    $opentag = "<!--$tag-->";
    $closetag = "<!--/$tag-->";
    if(is_int($i = strpos($s, $opentag)) && ($j = strpos($s, $closetag))) {
      $result = substr($s, $i + strlen($opentag), $j - $i - strlen($opentag));
      if ($replace === false) $replace = $result;
      $s = substr_replace($s, $replace, $i, $j - $i + strlen($closetag));
      $s = str_replace("\n\n", "\n", $s);
    }
    return $result;
  }
  
  public function gettag(&$s, $tag, $replace, $default = null) {
    if ($result = $this->parsetag($s, $tag, $replace)) return $result;
    return (string) $default;
  }
  
  public function deletespaces($s) {
    return trim(str_replace(
    array('   ', '  ', "\r", " \n", "\n\n"),
    array(' ', ' ', "\n", "\n", "\n"),
    $s));
  }
  
  private function fixhead($s) {
    return str_replace(array(
    '<link rel="alternate" type="application/rss+xml" title="$site.name RSS Feed" href="$site.url/rss.xml" />',
    '<link rel="pingback" href="$site.url/rpc.xml" />',
    '<link rel="EditURI" type="application/rsd+xml" title="RSD" href="$site.url/rsd.xml" />',
    '<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="$site.url/wlwmanifest.xml" />',
    '<link rel="shortcut icon" type="image/x-icon" href="$template.icon" />',
    '<link rel="shortcut icon" type="image/x-icon" href="$site.files/favicon.ico" />',
    '<meta name="generator" content="Lite Publisher $site.version" /> <!-- leave this for stats -->',
    '<meta name="keywords" content="$template.keywords" />',
    '<meta name="description" content="$template.description" />',
    '<link rel="sitemap" href="$site.url/sitemap.htm" />',
    '<script type="text/javascript" src="$site.files/js/litepublisher/rpc.min.js"></script>',
    '<script type="text/javascript" src="$site.files/js/litepublisher/client.min.js"></script>',
    '<script type="text/javascript" src="$site.files/js/jsibox/jsibox_basic.js"></script>',
    '<link rel="meta" type="application/rdf+xml" title="FOAF" href="$site.url/foaf.xml" />',
    
    ), '', $s);
  }
  
  private function parsetitle(&$s) {
    return $this->gettag($s, 'title', '$template.title', $this->default->title);
  }
  
  private function copy($section) {
    $result = &$this->theme->templates;
    foreach ($this->default->templates as $name => $value) {
      if (strbegin($name, $section)) $result[$name] = $value;
    }
  }
  
  private function append($section, array $a) {
    $result = &$this->theme->templates;
    foreach ($a as $name => $value) {
      if ($name != '') $name = '.' . $name;
      $result[$section . $name] = $value;
    }
  }
  
  private function parsemenu(&$str) {
    
    $result = &$this->theme->templates;
    $s = $this->parsetag($str, 'menulist', '$template.menu');
    if ($s == '') return $this->copy('menu');
    $default = &$this->default->templates;
    $item = trim($this->parsetag($s, 'item', '$item'));
    $result['menu.item.submenu'] = $this->parsetag($item, 'submenu', '$submenu', $default['menu.item.submenu']);
    $result['menu.item'] = $item != '' ? $item : $default['menu.item'];
    $result['menu.current'] = $this->parsetag($s, 'current', '', $default['menu.current']);
    //fix old version
    if ($this->fixold) {
      if (strpos($result['menu.item.submenu'], '%')) $result['menu.item.submenu'] = sprintf($result['menu.item.submenu'], '$items');
      if (strpos($result['menu.item'], '%')) $result['menu.item'] = sprintf($result['menu.item'], '$link', '$title', '$submenu');
      if (strpos($result['menu.current'], '%')) $result['menu.current'] = sprintf($result['menu.current'], '$link', '$title', '$submenu');
    }
    
    //hover
    $nohover = '<!--nohover-->';
    if (is_int($i = strpos($s, $nohover))) {
      $result['menu.hover'] = 'false';
      $s = substr_replace($s, '', $i, strlen($nohover));
    } elseif ($id = tcontentfilter::getidtag('*', $s)) {
      preg_match('/\<(\w*)/',$item, $t);
      $tag = $t[1];
      $result['menu.hover'] = 'true';
      $js = sprintf('		<script type="text/javascript">
      $(document).ready(function() {
    $("#%s %s").hover(function(){$(this).addClass("jshover");}, function(){$(this).removeClass("jshover");});
      });
      </script>', $id, $tag);
      $s = $js . $s;
    }
    
    $s = $this->deletespaces($s);
    if ($s == '') {
      if (!isset(    $result['menu.hover'])) $result['menu.hover'] = $menu->hover;
      $result['menu'] = $default['menu'];
    } else {
      if (!isset(    $result['menu.hover'])) $result['menu.hover'] = 'false';
      $result['menu'] = $s;
    }
  }
  
  private function parsecontent(&$str) {
    $s = $this->parsetag($str, 'content', '$template.content');
    $result = &$this->theme->templates;
    if ($s == '') return $this->copy('content');
    
    $this->parsepost($s);
    $this->parse_excerpts($s);
    $this->parsenavi($s);
    $this->parseadmin($s);
    
    $default = $this->default->templates;
    $result['content.menu']= $this->gettag($s, 'menu', '', $default['content.menu']);
    $result['content.simple'] = $this->gettag($s, 'simple', '', $default['content.simple']);
    $result['content.notfound'] = $this->gettag($s, 'notfound', '', $default['content.notfound']);
    if ($this->fixold) {
      if (strpos($result['content.simple'], '%')) $result['content.simple'] = sprintf($result['content.simple'], '$content');
      if (strpos($result['content.notfound'], '%')) $result['notfound'] = sprintf($result['content.notfound'], '$content');
    }
    
  }
  
  private function parse_excerpts(&$str) {
    $s = $this->parsetag($str, 'excerpts', '');
    if ($s == '') return $this->copy('content.excerpts');
    $this->parse_excerpt($s);
    $this->parselite($s);
    $s = $this->deletespaces($s);
    $result = &$this->theme->templates;
    $result['content.excerpts'] = $s != '' ? $s : (string) $this->default->content->excerpts;
  }
  
  private function parselite(&$str) {
    $s= $this->gettag($str, 'lite', '');
    if ($s == '') return $this->copy('content.excerpts.lite');
    $default = $this->default->content->excerpts->lite;
    $default->tostring = true;
    $result = &$this->theme->templates;
    $result['content.excerpts.lite.excerpt'] = $this->parsetag($s, 'excerpt', '$items', $default->excerpt);
    $s = $this->deletespaces($s);
    $s = str_replace('$items', '$excerpt', $s);
    $result['content.excerpts.lite'] = $s != '' ? $s : (string) $default;
  }
  
  private function parse_excerpt(&$str) {
    $s = $this->parsetag($str, 'excerpt', '$excerpt');
    if ($s == '') return $this->copy('content.excerpts.excerpt');
    $result = &$this->theme->templates;
    $path = 'content.excerpts.excerpt.';
    $categories = $this->parse_post_tags($s, 'categories', '$post.catlinks');
    $tags = $this->parse_post_tags($s, 'tags', '$post.taglinks');
    $common = $this->parse_post_tags($s, 'commontags', '');
    
    if ($categories) {
      $this->append($path . 'catlinks', $categories);
    } elseif ($common) {
      $this->append($path . 'catlinks', $common);
      $result[$path . 'catlinks'] = str_replace('commontags', 'categories', $common['']);
    } elseif ($tags) {
      $this->append($path . 'catlinks', $tags);
      $result[$path . 'catlinks'] = str_replace('tags', 'categories', $tags['']);
    } else {
      $this->copytags('catlinks');
    }
    
    if ($tags) {
      $this->append($path . 'taglinks', $tags);
    } elseif ($common) {
      $this->append($path . 'taglinks',  $common);
      $result[$path . 'taglinks'][0] = str_replace('commontags', 'tags', $common['']);
    } elseif ($categories) {
      $this->append($path . 'taglinks', $categories);
      $result[$path . 'taglinks'] = str_replace('categories', 'tags', $categories['']);
    } else {
      $this->copytags('taglinks');
    }
    
    $this->parsefilesexcerpt($s);
    $default = $this->default->content->excerpts->excerpt;
    $default->tostring = true;
    $result[$path . 'morelink'] = $this->gettag($s, 'more', '', $default->morelink);
    $result[$path . 'date'] = self::strftimetodate($this->parsetag($s, 'date', '$post.excerptdate', $result['content.post.date']));
    $s = $this->deletespaces($s);
    if ($this->fixold) $s = preg_replace('/\$post\.excerpt([^\w]+)/', '$post.excerptcontent$1', $s);
    $result['content.excerpts.excerpt'] = $s != '' ? $s : (string) $default;
  }
  
  private function parse_post_tags(&$s, $name, $replace) {
    $section = $this->parsetag($s, $name, $replace);
    if ($section == '') return false;
    $result = array();
    $result['item'] = trim($this->parsetag($section, 'item', '$items'));
    $result['divider'] = $this->parsetag($section, 'divider', '');
    $result[''] = trim($section);
    return $result;
  }
  
  private function copytags($name) {
    $result = &$this->theme->templates;
    $dst  = 'content.excerpts.excerpt.' . $name;
    $src = 'content.post.' . $name;
    foreach (array('', '.item', '.divider') as $key) {
      $result[$dst . $key] = $result[$src . $key];
    }
  }
  
  private function parsepost(&$str) {
    $s = $this->parsetag($str, 'post', '');
    if ($s == '') return $this->copy('content.post');
    $result = &$this->theme->templates;
    $default = $this->default->templates;
    
    $categories = $this->parse_post_tags($s, 'categories', '$post.catlinks');
    $tags = $this->parse_post_tags($s, 'tags', '$post.taglinks');
    $common = $this->parse_post_tags($s, 'commontags', '');
    
    if ($categories) {
      $this->append('content.post.catlinks', $categories);
    } elseif ($common) {
      $this->append('content.post.catlinks', $common);
      $result['content.post.catlinks'] = str_replace('commontags', 'categories', $common['']);
    } elseif ($tags) {
      $this->append('content.post.catlinks', $tags);
      $result['content.post.catlinks'] = str_replace('tags', 'categories', $tags['']);
    } else {
      $this->copy('content.post.catlinks');
    }
    
    if ($tags) {
      $this->append('content.post.taglinks', $tags);
    } elseif ($common) {
      $this->append('content.post.taglinks', $common);
      $result['content.post.taglinks'] = str_replace('commontags', 'tags', $common['']);
    } elseif ($categories) {
      $this->append('content.post.taglinks', $categories);
      $result['content.post.taglinks'] = str_replace('categories', 'tags', $categories['']);
    } else {
      $this->copy('content.post.taglinks');
    }
    
    $this->parsefiles('content.post.filelist', $s);
    $result['content.post.more'] = $this->gettag($s, 'more', '', $this->default->content->post->more);
    $result['content.post.rsslink'] = $this->gettag($s, 'rss', '$post.rsslink', $this->default->content->post->rsslink);
    $this->parseprevnext($s);
    $this->parsetemplatecomments($s);
    // after coments due to section 'date' in comment
    $result['content.post.date'] = self::strftimetodate($this->parsetag($s, 'date', '$post.date', $this->default->content->post->date));
    $s = trim($s);
    $result['content.post'] = $s != '' ? $s : (string) $this->default->content->post;
  }
  
  private function parsefiles($to, &$str) {
    $s = $this->parsetag($str, 'files', '$post.filelist');
    if ($s == '') return $this->copy($to);
    $default = $this->default->content->post->filelist;
    $default->tostring = true;
    
    $result = &$this->theme->templates;
    $result[$to . '.file'] = $this->gettag($s, 'file', '$file', $default->file);
    $result[$to . '.image'] = $this->gettag($s, 'image', '', $default->image);
    $result[$to . '.preview'] = $this->gettag($s, 'preview', '', $default->preview);
    $result[$to . '.audio'] = $this->gettag($s, 'audio', '', $default->audio);
    $result[$to . '.video'] = $this->parsetag($s, 'video', '', $default->video);
    $s = trim($s);
    $result[$to] = $s != '' ? $s : (string) $default;
  }
  
  private function parsefilesexcerpt(&$str) {
    $result = &$this->theme->templates;
    $files = array('' => $result['content.post.filelist']);
    foreach (array('file', 'image', 'preview', 'audio', 'video') as $name) {
      $files[$name] = $result['content.post.filelist.' . $name];
    }
    
    $s = $this->parsetag($str, 'files', '$post.excerptfilelist');
    if ($s == '') return $this->append('content.excerpts.excerpt.filelist', $files);
    $default = new tarray2prop();
    $default ->array = $files;
    $path = 'content.excerpts.excerpt.filelist.';
    $result[$path . 'file'] = $this->gettag($s, 'file', '$items', $default->file);
    $result[$path . 'image'] = $this->gettag($s, 'image', '', $default->image);
    $result[$path . 'preview'] = $this->gettag($s, 'preview', '', $default->preview);
    $result[$path . 'audio'] = $this->gettag($s, 'audio', '', $default->audio);
    $result[$path . 'video'] = $this->parsetag($s, 'video', '', $default->video);
    $s = trim($s);
    $result[trim($path, '.')] = $s != '' ? $s : $files[''];
  }
  
  private function parseprevnext(&$str) {
    $s = $this->parsetag($str, 'prevnext', '$post.prevnext');
    if ($s == '') return $this->copy('content.post.prevnext');
    $default = $this->default->content->post->prevnext;
    $default->tostring = true;
    $result = &$this->theme->templates;
    $result['content.post.prevnext.prev'] = $this->gettag($s, 'prev', '$prev', $default->prev);
    $result['content.post.prevnext.next'] = $this->parsetag($s, 'next', '$next', $default->next);
    $s = trim($s);
    $result['content.post.prevnext'] = $s != '' ? $s : (string) $default;
  }
  
  private function parsenavi(&$str) {
    $s = $this->parsetag($str, 'navi', '');
    if ($s == '') return $this->copy('content.navi');
    $default = $this->default->content->navi;
    $default->tostring = true;
    $path = 'content.navi.';
    $result = &$this->theme->templates;
    $result[$path . 'prev'] = $this->parsetag($s, 'prev', '$items', $default->prev);
    $result[$path . 'next'] = $this->parsetag($s, 'next', '', $default->next);
    $result[$path . 'link'] = $this->parsetag($s, 'link', '', $default->link);
    $result[$path . 'current'] = $this->parsetag($s, 'current', '', $default->current);
    $result[$path . 'divider'] = $this->parsetag($s, 'divider', '', $default->divider);
    if ($this->fixold) {
      $result[$path . 'prev'] = sprintf($result[$path . 'prev'], '$link');
      $result[$path . 'next'] = sprintf($result[$path . 'next'], '$link');
      $result[$path . 'link'] =sprintf($result[$path . 'link'], '$link', '$page');
      $result[$path . 'current'] =sprintf($result[$path . 'current'], '$link', '$page');
    }
    $s = $this->deletespaces($s);
    $result['content.navi'] = $s != '' ? $s : (string) $default;
  }
  
  private function parseadmin(&$str) {
    $s = $this->parsetag($str, 'admin', '');
    if ($s == '') return $this->copy('content.admin');
    $default = $this->default->content->admin;
    $default->tostring = true;
    $result = &$this->theme->templates;
    $path = 'content.admin.';
    $result[$path . 'editor'] = trim($this->gettag($s, 'editor', '', $default->editor));
    $result[$path . 'checkbox'] = trim($this->gettag($s, 'checkbox', '', $default->checkbox));
    $result[$path . 'text'] = trim($this->gettag($s, 'text', '', $default->text));
    $result[$path . 'combo'] = trim($this->gettag($s, 'combo', '', $default->combo));
    $result[$path . 'hidden'] = trim($this->gettag($s, 'hidden', '', $default->hidden));
    $result[$path . 'form'] = trim($this->gettag($s, 'form', '', $default->form));
  }
  
  private function parsetemplatecomments(&$str) {
    $s = $this->parsetag($str, 'templatecomments', '$post.templatecomments');
    if ($s == '') return $this->copy('content.post.templatecomments');
    $default = $this->default->templates;
    $result = &$this->theme->templates;
    $path = 'content.post.templatecomments.';
    $src = $this->parsetag($s, 'comments', '');
    if ($src == '') {
      $this->copy($path . 'comments');
    } else {
      
      $oldhold = $this->parsetag($src, 'hold', '');
      $this->parsecomments($src);
    }
    
    $src = $this->parsetag($s, 'moderateform', '');
    $result[$path . 'moderateform'] = $src == '' ? $default[$path . 'moderateform'] :
    '<script type="text/javascript" src="$site.files/js/litepublisher/rpc.min.js"></script>
    <script type="text/javascript" src="$site.files/js/litepublisher/moderate.min.js"></script>
    <script type="text/javascript" src="$site.files/files/admin$site.language.js"></script>' . $src;
    
    if ($src = $this->parsetag($s, 'pingbacks', '')) {
      $this->parsepingbacks($src);
    } else {
      $this->copy($path . 'pingbacks');
    }
    
    $src = $this->parsetag($s, 'closed', '');
    $result[$path . 'closed'] = $src == '' ? $default[$path . 'closed']  : $src;
    
    $src = $this->parsetag($s, 'form', '');
    $result[$path . 'form'] = $src == '' ? $default[$path . 'form']  :
    '<script type="text/javascript" src="$site.files/js/litepublisher/comments.min.js"></script>
    <script type="text/javascript" src="$site.files/files/$site.language.js"></script>' . $src;
    
    $src = $this->parsetag($s, 'confirmform', '');
    $result[$path . 'confirmform'] = $src == '' ? $default[$path . 'confirmform'] : $src;
    
    //fix holdcomments
    $hold = $result[$path . 'comments'];
    $id = $result[$path . 'comments.id'];
    $result[$path . 'comments.idhold'] = 'hold' . $id;
    $hold = str_replace("id=\"$id\"", "id=\"hold$id\"", $hold);
    $hold = str_replace('<a name="comments"', '<a name="holdcomments"', $hold);
    if (isset($oldhold)) $hold = $oldhold . $hold;
    $result[$path . 'holdcomments'] = $hold;
  }
  
  private function parsecomments($s) {
    $path = 'content.post.templatecomments.comments.';
    $default = $this->default->templates;
    $result = &$this->theme->templates;
    
    $src = $this->parsetag($s, 'count', '');
    $result[$path . 'count'] = $src == '' ? $default[$path . 'count'] : $src;
    
    if ($src = $this->parsetag($s, 'comment', '$comment')) {
      $this->parsecomment($src);
    } else {
      $this->copy($path . 'comment');
    }
    
    $src = $this->parsetag($s, 'commentsid', false);
    $result[$path . 'id'] = $src == '' ? $default[$path . 'id'] : $src;
    
    $s = $this->deletespaces($s);
    if ($this->fixold) $s = sprintf($s, '$items', '$from');
    $result['content.post.templatecomments.comments'] = $s == '' ? $default['content.post.templatecomments.comments'] : $s;
  }
  
  private function parsecomment($s) {
    $path = 'content.post.templatecomments.comments.comment.';
    $result = &$this->theme->templates;
    $default = $this->default->templates;
    $result[$path . 'class1'] = $this->gettag($s, 'class1', '$class', $default[$path . 'class1']);
    $result[$path . 'class2'] = $this->gettag($s, 'class2', '', $default[$path . 'class2']);
    $result[$path . 'moderate'] = $this->gettag($s, 'moderate', '$moderate', $default[$path . 'moderate']);
    $result[$path . 'date'] = self::strftimetodate($this->parsetag($s, 'date', '$comment.date', $default[$path . 'date']));
    $result[$path . 'quotebuttons'] = isset($default['quotebuttons']) ? $default[$path . 'quotebuttons'] : '';
    $s = $this->deletespaces($s);
    $path = rtrim($path, '.');
    $result[$path] = $s != '' ? $s : $default[$path];
  }
  
  private function parsepingbacks($s) {
    $path = 'content.post.templatecomments.pingbacks';
    $result = &$this->theme->templates;
    $default = $this->default->templates;
    $result[$path . '.pingback'] = $this->gettag($s, 'pingback', '$pingback', $default[$path . '.pingback']);
    $s = $this->deletespaces($s);
    $result[$path] = $s != '' ? $s : $default[$path];
  }
  
  private function parsesidebars(&$str) {
    $result = array();
    while ($sidebar = $this->parsetag($str, 'sidebar', '$template.sidebar')) {
      $result[] = $this->parsesidebar($sidebar, count($result));
    }
    if (count($result) == 0) return $this->default->templates['sidebars'];
    return $result;
  }
  
  private function parsesidebar($s, $sidebar) {
    $result = array();
    $default = &$this->default->templates['sidebars'][$sidebar];
    
    if ($widget = $this->parsetag($s, 'widget', '$items')) {
      $this->parsewidget($result, 'widget', $sidebar, $widget);
    } else {
      $this->copywidget($result, 'widget', $default, 'widget');
    }
    
    foreach (ttheme::getwidgetnames() as $name) {
      if ($widget =$this->parsetag($s, $name, ''))  {
        $this->parsewidget($result, $name, $sidebar, $widget);
      } else {
        $this->copywidget($result, $name, $result, 'widget');
        if (($name == 'posts') || ($name == 'comments')) {
          $result[$name . '.item']= $default[$name . '.item'];
        } elseif ($name == 'meta') {
          $result['meta.classes'] = $default['meta.classes'];
        }
      }
    }
    
    $s = $this->deletespaces($s);
    //$result[0] = $s != '' ? $s : $default[0];
    return $result;
  }
  
  private function copywidget(&$result, $name, $default, $defname) {
    foreach (array('', '.item', '.items', '.subitems') as $key) {
      $result[$name . $key] = $default[$defname . $key];
    }
  }
  
  private function parsewidget(&$result, $name, $sidebar, $s) {
    $default = $this->default->templates['sidebars'][$sidebar];
    if ($items = $this->parsetag($s, 'items', '$items')) {
      if ($item = $this->parsetag($items, 'item', '$item')) {
        if ($this->fixold) {
          $item = sprintf($item, '$link', '$title', '$text', '$subitems');
          $item = strtr($item, array(
          '$count' => '$subitems',
          '$url' => '$link',
          '$anchor' => '$text'
          ));
        }
        $result[$name . '.item'] = trim($item);
      } else {
        $result[$name . '.item'] = $default[$name . '.item'];
      }
      if ($name == 'meta') $result['meta.classes'] = $this->parsemetawidget($items, $sidebar);
      if ($this->fixold) $items = sprintf($items, '$item');
      $items = str_replace('$items', '$item', $items);
      $result[$name . '.items'] = $this->deletespaces($items);
    } else {
      $result[$name . '.items'] = $default[$name . '.items'];
      $result[$name . '.item'] = $default[$name . '.item'];
      if ($name == 'meta') $result['meta.classes'] = $default['meta.classes'];
    }
    
    $s = $this->deletespaces($s);
    if ($this->fixold) {
      $s = sprintf($s, '$title', '$items');
      $s = str_replace('$content', '$items', $s);
    }
    $result[$name] = $s != '' ? $s : $default[$name];
    $result[$name . '.subitems'] = $default[$name . '.subitems'];
  }
  
  private function parsemetawidget(&$str, $sidebar) {
    $s = $this->parsetag($str, 'metaclasses', '');
    return $s == '' ? $this->default->templates['sidebars'][$sidebar]['meta.classes'] : tthemeparser::getmetaclasses($s);
  }
  
  public static function strftimetodate($format) {
    static $trans;
    if (!isset($trans)) $trans = array(
    '%a' => 'D',
    '%A' => 'l',
    '%b' => 'M',
    '%B' => 'F',
    '%c' => tlocal::i()->ini['datetime']['dateformat'],
    '%C' => 'y',
    '%d' => 'd',
    '%D' => 'i/d/y',
    '%e' => 'j',
    '%g' => 'Y',
    '%G' => 'Y',
    '%h' => 'F',
    '%H' => 'H',
    '%I' => 'h',
    '%j' => 'z',
    '%m' => 'm',
    '%M' => 'i',
    '%n' => "\n",
    '%p' => 'A',
    '%r'  => 'ga.',
    '%R' => 'G',
    '%S' =>  's',
    '%t' => "\t",
    '%T' => 'H:i:s',
    '%u'=> 'w', // must be +1
    '%U' => 'W',
    '%V' => 'W',
    '%W' => 'W',
    '%w' => 'w',
    '%x' => tlocal::i()->ini['datetime']['dateformat'],
    '%X' => 'H:i:s',
    '%y' => 'y',
    '%Y' => 'Y',
    '%Z' => 't',
    '%%' => '%'
    );
    
    return strtr($format, $trans);
  }
  
  //aliases
}//class

class tdefaulttheme implements arrayaccess {
public function __get($name) { return $this; }
public function __set($name, $value) { }
public function __tostring() { return ''; }
  
public function offsetSet($offset, $value) {}
public function offsetExists($offset) { return true; }
public function offsetUnset($offset) {}
public function offsetGet($offset) { return $this; }
}//class

?>