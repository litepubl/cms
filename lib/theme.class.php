<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class ttheme extends basetheme {

  public static function i() {
    return getinstance(__class__);
  }

  public static function getinstance($name) {
    return self::getbyname(__class__, $name);
  }

  public static function context() {
    $result = self::i();
    if (!$result->name) {
      if (($context = litepublisher::$urlmap->context) && isset($context->idview)) {
        $result = tview::getview($context)->theme;
      } else {
        $result = tview::i()->theme;
      }
    }

    return $result;
  }

  public static function getwidgetnames() {
    return array(
      'categories',
      'tags',
      'archives',
      'links',
      'posts',
      'comments',
      'friends',
      'meta'
    );
  }

  protected function create() {
    parent::create();
    $this->templates = array(
      'index' => '',
      'title' => '',
      'menu' => '',
      'content' => '',
      'sidebars' => array() ,
      'custom' => array() ,
      'customadmin' => array()
    );
  }

  public function __tostring() {
    return $this->templates['index'];
  }

  public function getparser() {
    return tthemeparser::i();
  }

  public function getsidebarscount() {
    return count($this->templates['sidebars']);
  }
  private function get_author() {
    $context = isset(litepublisher::$urlmap->context) ? litepublisher::$urlmap->context : ttemplate::i()->context;
    if (!is_object($context)) {
      if (!isset(self::$vars['post'])) return new emptyclass();
      $context = self::$vars['post'];
    }

    if ($context instanceof tuserpages) return $context;
    $iduser = 0;
    foreach (array(
      'author',
      'idauthor',
      'user',
      'iduser'
    ) as $propname) {
      if (isset($context->$propname)) {
        $iduser = $context->$propname;
        break;
      }
    }
    if (!$iduser) return new emptyclass();
    $pages = tuserpages::i();
    if (!$pages->itemexists($iduser)) return new emptyclass();
    $pages->request($iduser);
    return $pages;
  }
  public function gethtml($context) {
    self::$vars['context'] = $context;
    if (isset($context->index_tml) && ($tml = $context->index_tml)) {
      return $this->parse($tml);
    }

    return $this->parse($this->templates['index']);
  }

  public function getnotfount() {
    return $this->parse($this->templates['content.notfound']);
  }

  public function getpages($url, $page, $count, $params = '') {
    if (!(($count > 1) && ($page >= 1) && ($page <= $count))) {
      return '';
    }

    $args = new targs();
    $args->count = $count;
    $from = 1;
    $to = $count;
    $perpage = litepublisher::$options->perpage;
    $args->perpage = $perpage;
    $items = array();
    if ($count > $perpage * 2) {
      //$page is midle of the bar
      $from = (int)max(1, $page - ceil($perpage / 2));
      $to = (int)min($count, $from + $perpage);
    }

    if ($from == 1) {
      $items = range($from, $to);
    } else {
      $items[0] = 1;
      if ($from > $perpage) {
        if ($from - $perpage - 1 < $perpage) {
          $items[] = $perpage;
        } else {
          array_splice($items, count($items) , 0, range($perpage, $from - 1, $perpage));
        }
      }
      array_splice($items, count($items) , 0, range($from, $to));
    }

    if ($to < $count) {
      $from2 = (int)($perpage * ceil(($to + 1) / $perpage));
      if ($from2 + $perpage >= $count) {
        if ($from2 < $count) $items[] = $from2;
      } else {
        array_splice($items, count($items) , 0, range($from2, $count, $perpage));
      }
      if ($items[count($items) - 1] != $count) $items[] = $count;
    }

    $currenttml = $this->templates['content.navi.current'];
    $tml = $this->templates['content.navi.link'];
    if (!strbegin($url, 'http')) $url = litepublisher::$site->url . $url;
    $pageurl = rtrim($url, '/') . '/page/';
    if ($params) $params = litepublisher::$site->q . $params;

    $a = array();
    if (($page > 1) && ($tml_prev = trim($this->templates['content.navi.prev']))) {
      $i = $page - 1;
      $args->page = $i;
      $link = $i == 1 ? $url : $pageurl . $i . '/';
      if ($params) $link.= $params;
      $args->link = $link;
      $a[] = $this->parsearg($tml_prev, $args);
    }

    foreach ($items as $i) {
      $args->page = $i;
      $link = $i == 1 ? $url : $pageurl . $i . '/';
      if ($params) $link.= $params;
      $args->link = $link;
      $a[] = $this->parsearg(($i == $page ? $currenttml : $tml) , $args);
    }

    if (($page < $count) && ($tml_next = trim($this->templates['content.navi.next']))) {
      $i = $page + 1;
      $args->page = $i;
      $link = $pageurl . $i . '/';
      if ($params) $link.= $params;
      $args->link = $link;
      $a[] = $this->parsearg($tml_next, $args);
    }

    $args->link = $url;
    $args->pageurl = $pageurl;
    $args->page = $page;
    $args->items = implode($this->templates['content.navi.divider'], $a);
    return $this->parsearg($this->templates['content.navi'], $args);
  }

  public function keyanounce($postanounce) {
    if (!$postanounce || $postanounce == 'excerpt' || $postanounce == 'default') return 'excerpt';
    if ($postanounce === true || $postanounce === 1 || $postanounce == 'lite') return 'lite';
    return 'card';
  }

  public function getposts(array $items, $postanounce) {
    if (!count($items)) return '';

    $result = '';
    $tml_key = $this->keyanounce($postanounce);
    tposts::i()->loaditems($items);

    self::$vars['lang'] = tlocal::i('default');
    //$tml = $lite ? $this->templates['content.excerpts.lite.excerpt'] : $this->templates['content.excerpts.excerpt'];
    foreach ($items as $id) {
      $post = tpost::i($id);
      $result.= $post->getcontexcerpt($tml_key);
      // has $author.* tags in tml
      if (isset(self::$vars['author'])) {
        unset(self::$vars['author']);
      }
    }

    if ($tml = $this->templates['content.excerpts' . ($tml_key == 'excerpt' ? '' : '.' . $tml_key) ]) {
      $result = str_replace('$excerpt', $result, $this->parse($tml));
    }

    unset(self::$vars['post']);
    return $result;
  }

  public function getpostsnavi(array $items, $url, $count, $postanounce, $perpage) {
    $result = $this->getposts($items, $postanounce);
    if (!$perpage) $perpage = litepublisher::$options->perpage;
    $result.= $this->getpages($url, litepublisher::$urlmap->page, ceil($count / $perpage));
    return $result;
  }

  public function getpostswidgetcontent(array $items, $sidebar, $tml) {
    if (count($items) == 0) return '';
    $result = '';
    if ($tml == '') $tml = $this->getwidgetitem('posts', $sidebar);
    foreach ($items as $id) {
      self::$vars['post'] = tpost::i($id);
      $result.= $this->parse($tml);
    }
    unset(self::$vars['post']);
    return str_replace('$item', $result, $this->getwidgetitems('posts', $sidebar));
  }

  public function getwidgetcontent($items, $name, $sidebar) {
    return str_replace('$item', $items, $this->getwidgetitems($name, $sidebar));
  }

  public function getwidget($title, $content, $template, $sidebar) {
    $args = new targs();
    $args->title = $title;
    $args->items = $content;
    $args->sidebar = $sidebar;
    return $this->parsearg($this->getwidgettml($sidebar, $template, '') , $args);
  }

  public function getidwidget($id, $title, $content, $template, $sidebar) {
    $args = new targs();
    $args->id = $id;
    $args->title = $title;
    $args->items = $content;
    $args->sidebar = $sidebar;
    return $this->parsearg($this->getwidgettml($sidebar, $template, '') , $args);
  }

  public function getwidgetitem($name, $index) {
    return $this->getwidgettml($index, $name, 'item');
  }

  public function getwidgetitems($name, $index) {
    return $this->getwidgettml($index, $name, 'items');
  }

  public function getwidgettml($index, $name, $tml) {
    $count = count($this->templates['sidebars']);
    if ($index >= $count) $index = $count - 1;
    $widgets = & $this->templates['sidebars'][$index];
    if (($tml != '') && ($tml[0] != '.')) $tml = '.' . $tml;
    if (isset($widgets[$name . $tml])) return $widgets[$name . $tml];
    if (isset($widgets['widget' . $tml])) return $widgets['widget' . $tml];
    $this->error("Unknown widget '$name' and template '$tml' in $index sidebar");
  }

  public function getajaxtitle($id, $title, $sidebar, $tml) {
    $args = new targs();
    $args->title = $title;
    $args->id = $id;
    $args->sidebar = $sidebar;
    return $this->parsearg($this->templates[$tml], $args);
  }

  public function simple($content) {
    return str_replace('$content', $content, $this->templates['content.simple']);
  }

  public function getbutton($title) {
    return strtr($this->templates['content.admin.button'], array(
      '$lang.$name' => $title,
      'name="$name"' => '',
      'id="submitbutton-$name"' => ''
    ));
  }

  public function getsubmit($title) {
    return strtr($this->templates['content.admin.submit'], array(
      '$lang.$name' => $title,
      'name="$name"' => '',
      'id="submitbutton-$name"' => ''
    ));
  }

  public function getinput($type, $name, $value, $title) {
    return strtr($this->templates['content.admin.' . $type], array(
      '$lang.$name' => $title,
      '$name' => $name,
      '$value' => $value
    ));
  }

  public function getradio($name, $value, $title, $checked) {
    return strtr($this->templates['content.admin.radioitem'], array(
      '$lang.$name' => $title,
      '$name' => $name,
      '$value' => $title,
      '$index' => $value,
      '$checked' => $checked ? 'checked="checked"' : '',
    ));
  }

  public static function getwidgetpath($path) {
    if ($path === '') return '';
    switch ($path) {
      case '.items':
        return '.items';

      case '.items.item':
      case '.item':
        return '.item';

      case '.items.item.subcount':
      case '.item.subcount':
      case '.subcount':
        return '.subcount';

      case '.items.item.subitems':
      case '.item.subitems':
      case '.subitems':
        return '.subitems';

      case '.classes':
      case '.items.classes':
        return '.classes';
    }

    return false;
  }

}

class emptyclass {
  public function __get($name) {
    return '';
  }
}