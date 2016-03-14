<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadminposts extends tadminmenu {
  private $isauthor;

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function canrequest() {
    $this->isauthor = false;
    if (!litepublisher::$options->hasgroup('editor')) {
      $this->isauthor = litepublisher::$options->hasgroup('author');
    }
  }

  public function getcontent() {
    if (isset($_GET['action']) && in_array($_GET['action'], array(
      'delete',
      'setdraft',
      'publish'
    ))) {
      return $this->doaction(tposts::i() , $_GET['action']);
    }

    return $this->gettable(tposts::i() , $where = "status <> 'deleted' ");
  }

  public function doaction($posts, $action) {
    $id = $this->idget();
    if (!$posts->itemexists($id)) return $this->notfound;
    $post = tpost::i($id);
    if ($this->isauthor && ($r = tauthor_rights::i()->changeposts($action))) return $r;
    if ($this->isauthor && (litepublisher::$options->user != $post->author)) return $this->notfound;
    if (!$this->confirmed) {
      $args = new targs();
      $args->id = $id;
      $args->adminurl = $this->adminurl;
      $args->action = $action;
      $args->confirm = sprintf($this->lang->confirm, $this->lang->$action, "<a href='$post->link'>$post->title</a>");
      return $this->html->confirmform($args);
    }

    $admintheme = $this->admintheme;
    switch ($_GET['action']) {
      case 'delete':
        $posts->delete($id);
        $result = $admintheme->h($lang->confirmeddelete);
        break;


      case 'setdraft':
        $post->status = 'draft';
        $posts->edit($post);
        $result = $admintheme->h($lang->confirmedsetdraft);
        break;


      case 'publish':
        $post->status = 'published';
        $posts->edit($post);
        $result = $admintheme->h($lang->confirmedpublish);
        break;
    }

    return $result;
  }

  public function gettable($posts, $where) {
    $perpage = 20;
    if ($this->isauthor) $where.= ' and author = ' . litepublisher::$options->user;
    $count = $posts->db->getcount($where);
    $from = $this->getfrom($perpage, $count);
    $items = $posts->select($where, " order by posted desc limit $from, $perpage");
    if (!$items) $items = array();

    $admintheme = $this->admintheme;
    $lang = tlocal::admin();
    $form = new adminform(new targs());
    $form->items = $admintheme->getcount($from, $from + count($items) , $count);

    $tb = new tablebuilder();
    $tb->setposts(array(
      array(
        'center',
        $lang->date,
        '$post.date'
      ) ,
      array(
        $lang->posttitle,
        '$post.bookmark'
      ) ,
      array(
        $lang->category,
        '$post.category'
      ) ,
      array(
        $lang->status,
        '$poststatus'
      ) ,
      array(
        $lang->edit,
        '<a href="' . tadminhtml::getadminlink('/admin/posts/editor/', 'id') . '=$post.id">' . $lang->edit . '</a>'
      ) ,
      array(
        $lang->delete,
        "<a class=\"confirm-delete-link\" href=\"$this->adminurl=\$post.id&action=delete\">$lang->delete</a>"
      ) ,
    ));

    $form->items.= $tb->build($items);
    $form->items.= $form->centergroup('[button=publish]
    [button=setdraft]
    [button=delete]');

    $form->submit = false;
    $result = $form->get();
    $result.= $this->theme->getpages('/admin/posts/', litepublisher::$urlmap->page, ceil($count / $perpage));
    return $result;
  }

  public function processform() {
    $posts = tposts::i();
    $posts->lock();
    $status = isset($_POST['publish']) ? 'published' : (isset($_POST['setdraft']) ? 'draft' : 'delete');
    if ($this->isauthor && ($r = tauthor_rights::i()->changeposts($status))) return $r;
    $iduser = litepublisher::$options->user;
    foreach ($_POST as $key => $id) {
      if (!is_numeric($id)) continue;
      $id = (int)$id;
      if ($status == 'delete') {
        if ($this->isauthor && ($iduser != $posts->db->getvalue('author'))) continue;
        $posts->delete($id);
      } else {
        $post = tpost::i($id);
        if ($this->isauthor && ($iduser != $post->author)) continue;
        $post->status = $status;
        $posts->edit($post);
      }
    }
    $posts->unlock();
  }

} //class