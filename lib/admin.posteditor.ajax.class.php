<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tajaxposteditor  extends tevents {
  public $idpost;
  private $isauthor;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'ajaxposteditor';
    $this->addevents('onhead', 'oneditor');
    $this->data['head'] = '';
    $this->data['visual'] = '';
    //'/plugins/tiny_mce/init.js';
    //'/plugins/ckeditor/init.js';
    $this->data['ajaxvisual'] = true;
  }
  
  public function setvisual($url) {
    if ($url == $this->visual) return;
    $js = tjsmerger::i();
    $js->lock();
    $js->deletefile('posteditor', $this->visual);
    $js->deletetext('posteditor', 'visual');
    
    if ($url) {
      if ($this->ajaxvisual) {
        $js->addtext('posteditor', 'visual', sprintf(
        '$(document).ready(function() {
          litepubl.posteditor.init_visual_link("%s", %s);
        });', litepublisher::$site->files . $url, json_encode(tlocal::get('editor', 'loadvisual')))
        );
      }else {
        $js->add('posteditor', $url);
      }
    }
    
    $js->unlock();
    
    $this->data['visual'] = $url;
    $this->save();
  }
  
  public function gethead() {
    $result = $this->data['head'];
    if ($this->visual) {
      $template = ttemplate::i();
      if ($this->ajaxvisual) {
        $result .= $template->getready('$("a[rel~=\'loadvisual\']").one("click", function() {
          $("#loadvisual").remove();
          $.load_script("' . litepublisher::$site->files . $this->visual . '");
          return false;
        });');
      } else {
        $result .= $template->getjavascript($this->visual);
      }
    }
    
    $this->callevent('onhead', array(&$result));
    return $result;
  }
  
  protected static function error403() {
    return '<?php header(\'HTTP/1.1 403 Forbidden\', true, 403); ?>' . turlmap::htmlheader(false) . 'Forbidden';
  }
  
  public function getviewicon($idview, $icon) {
    $result = tadminviews::getcomboview($idview);
    if ($icons = tadminicons::getradio($icon)) {
      $html = tadminhtml ::i();
      if ($html->section == '') $html->section = 'editor';
      $result .= $html->h2->icons;
      $result .= $icons;
    }
    return $result;
  }
  
  public static function auth() {
    $options = litepublisher::$options;
    if (!$options->user) return self::error403();
    if (!$options->hasgroup('editor')) {
      if (!$options->hasgroup('author')) return self::error403();
    }
  }
  
  public function request($arg) {
    $this->cache = false;
    //tfiler::log(var_export($_GET, true) . var_export($_POST, true) . var_export($_FILES, true));
    if ($err = self::auth()) return $err;
    $this->idpost = tadminhtml::idparam();
    $this->isauthor = litepublisher::$options->ingroup('author');
    if ($this->idpost > 0) {
      $posts = tposts::i();
      if (!$posts->itemexists($this->idpost)) return self::error403();
      if (!litepublisher::$options->hasgroup('editor')) {
        if (litepublisher::$options->hasgroup('author')) {
          $this->isauthor = true;
          $post = tpost::i($this->idpost);
          if (litepublisher::$options->user != $post->author) return self::error403();
        }
      }
    }
    return $this->getcontent();
  }
  
  public function getcontent() {
    $theme = tview::i(tviews::i()->defaults['admin'])->theme;
    $html = tadminhtml ::i();
    $html->section = 'editor';
    $lang = tlocal::i('editor');
    $post = tpost::i($this->idpost);
    ttheme::$vars['post'] = $post;
    
    switch ($_GET['get']) {
      case 'tags':
      $result = $html->getedit('tags', $post->tagnames, $lang->tags);
      $lang->section = 'editor';
      $result .= $html->h4->addtags;
      $items = array();
      $tags = $post->factory->tags;
      $list = $tags->getsorted(-1, 'name', 0);
      foreach ($list as $id ) {
        $items[] = '<a href="" class="posteditor-tag">' . $tags->items[$id]['title'] . "</a>";
      }
      $result .= sprintf('<p>%s</p>', implode(', ', $items));
      break;
      
      case 'status':
      $args = new targs();
      $args->comstatus= tadminhtml::array2combo(array(
      'closed' => $lang->closed,
      'reg' => $lang->reg,
      'guest' => $lang->guest,
      'comuser' => $lang->comuser
      ), $post->comstatus);
      
      
      $args->pingenabled = $post->pingenabled;
      $args->status= tadminhtml::array2combo(array(
      'published' => $lang->published,
      'draft' => $lang->draft
      ), $post->status);
      
      $args->perms = tadminperms::getcombo($post->idperm);
      $args->password = $post->password;
      $result = $html->parsearg(
      '[combo=comstatus]
      [checkbox=pingenabled]
      [combo=status]
      $perms
      [password=password]
      <p>$lang.notepassword</p>',
      $args);
      
      break;
      
      case 'view':
      $result = $this->getviewicon($post->idview, $post->icon);
      break;
      
      default:
      $result = var_export($_GET, true);
    }
    //tfiler::log($result);
    return turlmap::htmlheader(false) . $result;
  }
  
  public function geteditor($name, $value, $visual) {
    $html = tadminhtml ::i();
    $hsect = $html->section;
    $html->section = 'editor';
    $lang = tlocal::i();
    $lsect = $lang->section;
    $lang->section = 'editor';
    $title = $lang->$name;
    if ($visual && $this->ajaxvisual && $this->visual) $title .= $html->loadvisual();
    $result = $html->getinput('editor', $name, tadminhtml::specchars($value), $title);
    $lang->section = $lsect;
    $html->section = $hsect;
    return $result;
  }
  
}//class