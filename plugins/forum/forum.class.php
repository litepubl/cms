<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tforum extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function create() {
    parent::create();
    $this->data['idview'] = 1;
    $this->data['idperm'] = 0;
    $this->data['rootcat'] = 0;
    $this->data['moderate'] = false;
    $this->data['comstatus'] = 'reg';
    $this->data['comboitems'] = '';
  }
  
  public function themeparsed(ttheme $theme) {
    if (($theme->name == 'forum') && !strpos($theme->templates['content.post'], '$forum.comboitems')) {
      $html = tadminhtml::i();
      $section = $html->section;
      $html->push_section('forum');
      $lang = tlocal::admin('forum');
      
      $combo = str_replace('\'', '"', $theme->parse($html->combocats));
      //$this->categories_changed();
      
      $theme->templates['content.post'] .= $combo;
      $theme->templates['content.excerpts'] .= $combo;
      
      $theme->templates['content.post'] = str_replace('\'', '"', str_replace('$post.content',
      '$post.content ' . $theme->replacelang($html->editlink, $lang),
      $theme->templates['content.post']));
      
      $theme->templates['index'] = str_replace('$custom.breadcrumbs', '$forum.breadcrumbs',
      $theme->templates['index']);
      $html->pop_section();
    }
  }
  
  public function catadded($id) {
    //set idview to forum
    $cats = tcategories::i();
    if ($this->idview == $cats->getvalue($id, 'idview')) return;
    $cats->loadall();
    $idparent = $id;
    while ($idparent = (int) $cats->items[$idparent]['parent']) {
      if ($idparent == $this->rootcat) {
        $cats->setvalue($id, 'idview', $this->idview);
        break;
      }
    }
  }
  
  public function categories_changed() {
    $this->comboitems = $this->getcats($this->rootcat, '');
    $this->save();
  }
  
  public function getcats($idparent, $pretitle) {
    $result = '';
    $cats = tcategories::i();
    $cats->loadall();
    $items = $cats->db->idselect("parent = $idparent order by title asc");
    foreach ($items as $id) {
      $item = $cats->getitem($id);
      $result .= sprintf('<option value="%s" data-url="%s">%s%s</option>', $id, $item['url'], $pretitle, $item['title']);
      $result .= $this->getcats($id, $item['title'] . ' / ');
    }
    return $result;
  }
  
  
  public function getbreadcrumbs() {
    $context = litepublisher::$urlmap->context;
    if ($context instanceof tpost) {
      $idcat = (int) $context->idcat;
    } elseif ($context instanceof tcategories) {
      $idcat = (int) $context->getvalue($context->id, 'parent');
    } else {
      $idcat = 0;
    }
    
    if ($idcat == 0) return '';
    $filename = $idcat . '.breadcrumbs.php';
    if ($result = litepublisher::$urlmap->cache->get($filename)) return $result;
    $result = $this->build_breadcrumbs($idcat);
    litepublisher::$urlmap->cache->set($filename, $result);
    return $result;
  }
  
  public function build_breadcrumbs($id) {
    $cats = tcategories::i();
    $cats->loadall();
    $list = array($id);
    while ($id = (int) $cats->items[$id]['parent']) {
      array_unshift($list, $id);
    }
    
    $tml = '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" href="$site.url$url" title="$title">$title</a></span>';
    $theme = ttheme::i();
    $args = new targs();
    $result = '';
    foreach ($list as $id) {
      $args->add($cats->items[$id]);
      $result .= $theme->parsearg($tml, $args);
    }
    return $result;
  }
  
}//class