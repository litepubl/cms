<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmintags extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $istags = ($this->name == 'tags' ) || ($this->name == 'addtag');
    $tags = $istags  ? litepublisher::$classes->tags : litepublisher::$classes->categories;
    if (dbversion) $tags->loadall();
    $parents = array(0 => '-----');
    foreach ($tags->items as $id => $item) {
      $parents[$id] = $item['title'];
    }
    
    $this->basename = 'tags';
    $html = $this->html;
    $lang = tlocal::i('tags');
    $id = $this->idget();
    $args = new targs();
    $args->id = $id;
    $args->adminurl = $this->adminurl;
    $ajax = tadminhtml::getadminlink('/admin/ajaxtageditor.htm', sprintf('id=%d&type=%s&get', $id, $istags  ? 'tags' : 'categories'));
    $args->ajax = $ajax;
    
    if (isset($_GET['action']) && ($_GET['action'] == 'delete') && $tags->itemexists($id)) {
      if  ($this->confirmed) {
        $tags->delete($id);
        $result .= $html->h4->successdeleted;
      } else {
        return $html->confirmdelete($id, $this->adminurl, $lang->confirmdelete);
      }
    }
    
    $result .= $html->h4(tadminhtml::getlink('/admin/posts/' . ($istags ? 'addtag' : 'addcat') . '/',  $lang->add));
    $item = false;
    if ($id && $tags->itemexists($id)) {
      $item = $tags->getitem($id);
      $args->formtitle = $lang->edit;
    } elseif (($this->name == 'addcat') || ($this->name == 'addtag')) {
      $id = 0;
      $item = array(
      'id' => 0,
      'title' => '',
      'parent' => 0,
      'customorder' => 0,
      );
      $args->formtitle = $lang->add;
    }
    
    if ($item) {
      $args->add($item);
      $args->parent = tadminhtml::array2combo($parents, $item['parent']);
      $args->order = tadminhtml::array2combo(array_combine(range(0, 9), range(1,10)), $item['customorder']);
      
      $tabs = new tuitabs();
      $tabs->add($lang->title, '
      [text=title]
      [combo=parent]
      [combo=order]
      [hidden=id]' .
      $html->p->ordernote);
      
      $tabs->ajax($lang->text, "$ajax=text");
      $tabs->ajax($lang->view, "$ajax=view");
      $tabs->ajax('SEO', "$ajax=seo");
      
      $form = new adminform($args);
      $result .= $html->adminform($tabs->get(), $args) .
      tuitabs::gethead();
    }
    
    //table
    $perpage = 20;
    $count = $tags->count;
    $from = $this->getfrom($perpage, $count);
    if ($tags->dbversion) {
      $iditems = $tags->db->idselect("id > 0 order by parent asc, title asc limit $from, $perpage");
    } else {
      $iditems = array_slice(array_keys($tags->items), $from, $perpage);
    }
    
    $items = array();
    foreach ($iditems as $id) {
      $item = $tags->items[$id];
      $item['parentname'] = $parents[$item['parent']];
      $items[] = $item;
    }
    
    $result .= $html->buildtable($items, array(
    array('right', $lang->count2, '$itemscount'),
    array('left', $lang->title,'<a href="$link" title="$title">$title</a>'),
    array('left', $lang->parent, '$parentname'),
    array('center', $lang->edit, "<a href=\"$this->adminurl=\$id\">$lang->edit</a>"),
    array('center', $lang->delete, "<a class=\"confirm-delete-link\" href=\"$this->adminurl=\$id&action=delete\">$lang->delete</a>")
    ));
    $result = $html->fixquote($result);
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  private function set_view(array $item) {
    extract($_POST, EXTR_SKIP);
    $item['idview'] = (int) $idview;
    $item['includechilds'] = isset($includechilds);
    $item['includeparents'] = isset($includeparents);
    $item['invertorder'] = isset($invertorder);
    $item['lite'] = isset($lite);
    $item['liteperpage'] = (int) trim($liteperpage);
    if (isset($idperm)) $item['idperm'] = (int) $idperm;
    if (isset($icon)) $item['icon'] = (int) $icon;
    return $item;
  }
  
  public function processform() {
    if (empty($_POST['title'])) return '';
    extract($_POST, EXTR_SKIP);
    $istags = ($this->name == 'tags' ) || ($this->name == 'addtag');
    $tags = $istags  ? litepublisher::$classes->tags : litepublisher::$classes->categories;
    $tags->lock();
    $id = $this->idget();
    if ($id == 0) {
      $id = $tags->add((int) $parent, $title);
      if (isset($order)) $tags->setvalue($id, 'customorder', (int) $order);
      if (isset($url)) $tags->edit($id, $title, $url);
      if (isset($idview)) {
        $item =$tags->getitem($id);
        $item = $this->set_view($item);
        $tags->items[$id] = $item;
        $item['id'] = $id;
        unset($item['url']);
        if ($tags->dbversion) $tags->db->updateassoc($item);
      }
    } else {
      $item = $tags->getitem($id);
      $item['title'] = $title;
      if (isset($parent)) $item['parent'] = (int) $parent;
      if (isset($order)) $item['customorder'] = (int) $order;
      if (isset($idview)) $item = $this->set_view($item);
      $tags->items[$id] = $item;
      if (!empty($url) && ($url != $item['url'])) $tags->edit($id, $title, $url);
      $tags->items[$id] = $item;
      if (dbversion) {
        unset($item['url']);
        $tags->db->updateassoc($item);
      }
    }
    
    if (isset($raw) || isset($keywords)) {
      $item = $tags->contents->getitem($id);
      if (isset($raw)) {
        $filter = tcontentfilter::i();
        $item['rawcontent'] = $raw;
        $item['content'] = $filter->filterpages($raw);
      }
      if (isset($keywords)) {
        $item['keywords'] = $keywords;
        $item['description'] = $description;
        $item['head'] = $head;
      }
      $tags->contents->setitem($id, $item);
    }
    
    $tags->unlock();
    $_GET['id'] = $_POST['id'] = $id;
    return sprintf($this->html->h2->success, $title);
  }
  
}//class