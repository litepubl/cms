<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class thomepage extends tsinglemenu {
  public $cacheposts;
  public $midleposts;

  public static function i($id = 0) {
    return static::iteminstance(__class__, $id);
  }

  protected function create() {
    parent::create();
    $this->basename = 'homepage';
    $this->data['image'] = '';
    $this->data['smallimage'] = '';
    $this->data['showmidle'] = false;
    $this->data['midlecat'] = 0;
    $this->data['showposts'] = true;
    $this->data['includecats'] = array();
    $this->data['excludecats'] = array();
    $this->data['showpagenator'] = true;
    $this->data['archcount'] = 0;
    $this->data['parsetags'] = false;
    $this->coinstances[] = new tcoevents($this, 'onbeforegetitems', 'ongetitems');
    $this->cacheposts = false;
    $this->midleposts = false;
  }

  public function getindex_tml() {
    $theme = ttheme::i();
    if (!empty($theme->templates['index.home'])) return $theme->templates['index.home'];
    return false;
  }

  public function request($id) {
    if (!$this->showpagenator && (litepubl::$urlmap->page > 1)) return 404;
    return parent::request($id);
  }

  public function gethead() {
    $result = parent::gethead();

    $theme = tview::getview($this)->theme;
    $result.= $theme->templates['head.home'];

    if ($this->showposts) {
      $items = $this->getidposts();
      $result.= tposts::i()->getanhead($items);
    }

    ttheme::$vars['home'] = $this;
    return $theme->parse($result);
  }

  public function gettitle() {
  }

  public function getbefore() {
    if ($result = $this->content) {
      $theme = ttheme::i();
      $result = $theme->simple($result);
      if ($this->parsetags || litepubl::$options->parsepost) {
        $result = $theme->parse($result);
      }

      return $result;
    }

    return '';
  }

  public function getcont() {
    $result = '';
    if (litepubl::$urlmap->page == 1) {
      $result.= $this->getbefore();
      if ($this->showmidle && $this->midlecat) {
        $result.= $this->getmidle();
      }
    }

    if ($this->showposts) {
      $result.= $this->getpostnavi();
    }

    return $result;
  }

  public function getpostnavi() {
    $items = $this->getidposts();
    $view = tview::getview($this);
    $result = $view->theme->getposts($items, $view->postanounce);
    if ($this->showpagenator) {
      $perpage = $view->perpage ? $view->perpage : litepubl::$options->perpage;
      $result.= $view->theme->getpages($this->url, litepubl::$urlmap->page, ceil($this->data['archcount'] / $perpage));
    }
    return $result;
  }

  public function getidposts() {
    if (is_array($this->cacheposts)) return $this->cacheposts;
    if ($result = $this->onbeforegetitems()) return $result;
    $posts = tposts::i();
    $view = tview::getview($this);
    $perpage = $view->perpage ? $view->perpage : litepubl::$options->perpage;
    $from = (litepubl::$urlmap->page - 1) * $perpage;
    $order = $view->invertorder ? 'asc' : 'desc';

    $p = litepubl::$db->prefix . 'posts';
    $ci = litepubl::$db->prefix . 'categoriesitems';

    if ($where = $this->getwhere()) {
      $result = $posts->db->res2id($posts->db->query("select $p.id as id, $ci.item as item from $p, $ci
      where    $where and $p.id = $ci.post and $p.status = 'published'
      order by  $p.posted $order limit $from, $perpage"));

      $result = array_unique($result);
      $posts->loaditems($result);
    } else {
      $this->data['archcount'] = $posts->archivescount;
      $result = $posts->getpage(0, litepubl::$urlmap->page, $perpage, $view->invertorder);
    }

    $this->callevent('ongetitems', array(&$result
    ));
    $this->cacheposts = $result;
    return $result;
  }

  public function getwhere() {
    $result = '';
    $p = litepubl::$db->prefix . 'posts';
    $ci = litepubl::$db->prefix . 'categoriesitems';
    if ($this->showmidle && $this->midlecat) {
      $ex = $this->getmidleposts();
      if (count($ex)) $result.= sprintf('%s.id not in (%s) ', $p, implode(',', $ex));
    }

    $include = $this->data['includecats'];
    $exclude = $this->data['excludecats'];

    if (count($include) > 0) {
      if ($result) $result.= ' and ';
      $result.= sprintf('%s.item  in (%s)', $ci, implode(',', $include));
    }

    if (count($exclude) > 0) {
      if ($result) $result.= ' and ';
      $result.= sprintf('%s.item  not in (%s)', $ci, implode(',', $exclude));
    }

    return $result;
  }

  public function postschanged() {
    if (!$this->showposts || !$this->showpagenator) return;

    if ($where = $this->getwhere()) {
      $db = $this->db;
      $p = litepubl::$db->prefix . 'posts';
      $ci = litepubl::$db->prefix . 'categoriesitems';

      $res = $db->query("select count(DISTINCT $p.id) as count from $p, $ci
      where    $where and $p.id = $ci.post and $p.status = 'published'");

      if ($r = $res->fetch_assoc()) $this->data['archcount'] = (int)$r['count'];
    } else {
      $this->data['archcount'] = tposts::i()->archivescount;
    }

    $this->save();
  }

  public function getmidletitle() {
    if ($idcat = $this->midlecat) {
      return $this->getdb('categories')->getvalue($idcat, 'title');
    }

    return '';
  }

  public function getmidleposts() {
    if (is_array($this->midleposts)) return $this->midleposts;
    $posts = tposts::i();
    $p = $posts->thistable;
    $ci = litepubl::$db->prefix . 'categoriesitems';
    $this->midleposts = $posts->db->res2id($posts->db->query("select $p.id as id, $ci.post as post from $p, $ci
    where    $ci.item = $this->midlecat and $p.id = $ci.post and $p.status = 'published'
    order by  $p.posted desc limit " . litepubl::$options->perpage));

    if (count($this->midleposts)) $posts->loaditems($this->midleposts);
    return $this->midleposts;
  }

  public function getmidle() {
    $result = '';
    $items = $this->getmidleposts();
    if (!count($items)) return '';
    ttheme::$vars['lang'] = tlocal::i('default');
    ttheme::$vars['home'] = $this;
    $theme = ttheme::i();
    $tml = $theme->templates['content.home.midle.post'];
    foreach ($items as $id) {
      ttheme::$vars['post'] = tpost::i($id);
      $result.= $theme->parse($tml);
      // has $author.* tags in tml
      if (isset(ttheme::$vars['author'])) unset(ttheme::$vars['author']);
    }

    $tml = $theme->templates['content.home.midle'];
    if ($tml) {
      $args = new targs();
      $args->post = $result;
      $args->midletitle = $this->midletitle;
      $result = $theme->parsearg($tml, $args);
    }

    unset(ttheme::$vars['post'], ttheme::$vars['home']);
    return $result;
  }

} //class