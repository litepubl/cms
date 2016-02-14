<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadmintickets extends tadminmenu {

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function getcontent() {
    $result = '';
    $tickets = ttickets::i();
    $perpage = 20;
    $where = litepublisher::$options->group == 'ticket' ? ' and author = ' . litepublisher::$options->user : '';

    switch ($this->name) {
      case 'opened':
        $where.= " and state = 'opened' ";
        break;


      case 'fixed':
        $where.= " and state = 'fixed' ";
        break;
    }

    $count = $tickets->getchildscount($where);
    $from = $this->getfrom($perpage, $count);

    if ($count > 0) {
      $items = $tickets->select("status <> 'deleted' $where", " order by posted desc limit $from, $perpage");
      if (!$items) $items = array();
    } else {
      $items = array();
    }

$admintheme = $this->admintheme;
    $lang = tlocal::admin('tickets');
    $lang->addsearch('ticket', 'tickets');
    $result.= $admintheme->h($admintheme->link('/admin/tickets/editor/', $lang->editortitle));
    $result.= $this->html->getitemscount($from, $from + count($items) , $count);

    $tb = new tablebuilder();
    $tb->setposts(array(
      array(
        'right',
        $lang->date,
        '$post.date'
      ) ,

      array(
        $lang->posttitle,
        '$post.bookmark'
      ) ,

      array(
        $lang->author,
        '$post.authorlink'
      ) ,

      array(
        $lang->status,
        '$poststatus'
      ) ,

      array(
        $lang->category,
        '$post.category'
      ) ,

      array(
        $lang->state,
        function (tablebuilder $t) {
          return tlocal::i()->__get(basetheme::$vars['post']->state);
        }
      ) ,

      array(
        $lang->edit,
        '<a href="' . tadminhtml::getadminlink('/admin/tickets/editor/', 'id') . '=$post.id">' . $lang->edit . '</a>'
      ) ,

    ));

    $table = $tb->build($items);

    //wrap form
    if (litepublisher::$options->group != 'ticket') {
      $args = new targs();
$form = new adminform($args);
$form->body = $table;
    $form->body .= $form->centergroup($this->html->getsubmit('delete', 'setdraft', 'publish', 'setfixed'));
    $form->submit = '';
      $result.= $form->get();
    } else {
      $result.= $table;
    }

    $theme = $this->theme;
    $result.= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count / $perpage));
    return $result;
  }

  public function processform() {
    if (litepublisher::$options->group == 'ticket') return '';
    $tickets = ttickets::i();
    $status = isset($_POST['publish']) ? 'published' : (isset($_POST['setdraft']) ? 'draft' : (isset($_POST['setfixed']) ? 'fixed' : 'delete'));
    foreach ($_POST as $key => $id) {
      if (!is_numeric($id)) continue;
      $id = (int)$id;
      if ($status == 'delete') {
        $tickets->delete($id);
      } else {
        $ticket = tticket::i($id);
        if ($status == 'fixed') {
          $ticket->set_state($status);
        } else {
          $ticket->status = $status;
        }
        $tickets->edit($ticket);
      }
    }
  }

} //class