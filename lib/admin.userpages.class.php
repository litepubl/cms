<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadminuserpages extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function  gethead() {
    return parent::gethead() . tuitabs::gethead();
  }
  
  public function getiduser() {
    if (litepublisher::$options->ingroup('admin')) {
      $id = $this->idget();
    } else {
      $id = litepublisher::$options->user;
    }
    
    $users = tusers::i();
    if ($users->itemexists($id) && ('approved' == $users->getvalue($id, 'status'))) return $id;
    return false;
  }
  
  public function getcontent() {
    $result = '';
    $users = tusers::i();
    $html = $this->gethtml('users');
    $lang = tlocal::admin('users');
    $args = new targs();
    
    if (!($id= $this->getiduser())) {
      if (litepublisher::$options->ingroup('admin')) return $this->getuserlist();
      return $this->notfound;
    }
    
    $pages = tuserpages::i();
    $item = tusers::i()->getitem($id) + $pages->getitem($id);
    if (!isset($item['url'])) {
      $item['url'] = $item['idurl'] ? litepublisher::$urlmap->getidurl($item['idurl']) : '';
    }
    $args->add($item);
    $args->formtitle = sprintf('<a href="$site.url%s">%s</a>', $item['url'], $item['name']);
    $tabs = new tuitabs();
    $tabs->add($lang->title, '[text=name] [text=website]');
    if ('admin' == litepublisher::$options->group) {
      $tabs->add($lang->view, tadminviews::getcomboview($item['idview']));
      $tabs->add('SEO', '[text=url] [text=keywords] [text=description] [editor=head]');
    }
    $tabs->add($lang->text, '[editor=rawcontent]');
    
    $opt = tuseroptions::i()->getitem($id);
    $args->subscribe = $opt['subscribe'] == 'enabled';
    $args->authorpost_subscribe = $opt['authorpost_subscribe'] == 'enabled';
    $tabs->add($lang->options, '
    [checkbox=subscribe]
    [checkbox=authorpost_subscribe]
    ');
    
    return $html->adminform($tabs->get(), $args);
  }
  
  public function processform() {
    extract($_POST, EXTR_SKIP);
    if (!($id= $this->getiduser())) return;
    $item = array(
    'rawcontent' => trim($rawcontent),
    'content' => tcontentfilter::i()->filter($rawcontent)
    );
    
    if ('admin' == litepublisher::$options->group) {
      $item['idview'] = (int) $idview;
      $item['url'] = $url;
      $item['head'] = $head;
      $item['keywords'] = $keywords;
      $item['description'] = $description;
    }
    
    $pages = tuserpages::i();
    $pages->edit($id, $item);
    
    tusers::i()->edit($id, array(
    'name' => $name,
    'website' => tcontentfilter::clean_website($website),
    ));
    
    $useroptions = tuseroptions::i();
    $useroptions->setvalue($id, 'subscribe', isset($subscribe) ? 'enabled' : 'disabled');
    $useroptions->setvalue($id, 'authorpost_subscribe', isset($authorpost_subscribe) ? 'enabled' : 'disabled');
  }
  
  public function getuserlist() {
    $users = tusers::i();
    $pages = tuserpages::i();
    $perpage = 20;
    $count = $pages->count;
    $from = $this->getfrom($perpage, $count);
    $p = $pages->thistable;
    $u = $users->thistable;
    $items = $users->res2items($users->db->query("
    select $u.*  from $u
    left join $p on $u.id = $p.id
    where not $p.id is null
    order by $u.id desc limit $from, $perpage"));
    
    $html = $this->gethtml('users');
    $lang = tlocal::admin('users');
    $args = new targs();
    $args->adminurl = $this->adminurl;
    $result = $html->h4->userstable;
    
    $tb = new tablebuilder();
    $tb->setowner($users);
    $tb->setstruct(array(
    array(
    $lang->edit,
    sprintf('<a href="%s=$id">$name</a>', $this->adminurl)
    )
    ));
    
    $result .= $tb->build($items);
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
}//class