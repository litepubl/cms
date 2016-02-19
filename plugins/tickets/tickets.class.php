<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class ttickets extends tposts {
  public $cats;

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->childtable = 'tickets';
    $this->addmap('cats', array());
    $this->data['idcomauthor'] = 0;
  }

  public function newpost() {
    return tticket::i();
  }

  public function createpoll($id) {
    return polls::i()->add('like', $id, 'post');
  }

  public function filtercats(tpost $post) {
    $cats = array_intersect($post->categories, $this->cats);
    if (count($cats) == 0) {
      $cats = array(
        $this->cats[0]
      );
    } elseif (count($cats) > 1) {
      $cats = array(
        $cats[0]
      );
    }

    $post->categories = $cats;
  }

  public function add(tpost $post) {
    $this->filtercats($post);
    $post->updatefiltered();

    $id = parent::add($post);
    $this->createpoll($id);
    $this->notify($post);
    return $id;
  }

  private function notify(tticket $ticket) {
    ttheme::$vars['ticket'] = $ticket;
    $args = new targs();
    $args->adminurl = litepublisher::$site->url . '/admin/tickets/editor/' . litepublisher::$site->q . 'id=' . $ticket->id;

    tlocal::usefile('mail');
    $lang = tlocal::i('mailticket');
    $lang->addsearch('ticket');
    $theme = ttheme::i();

    $subject = $theme->parsearg($lang->subject, $args);
    $body = $theme->parsearg($lang->body, $args);

    tmailer::sendtoadmin($subject, $body);
  }

  public function edit(tpost $post) {
    $this->filtercats($post);
    $post->updatefiltered();
    return parent::edit($post);
  }

  public function onexclude($id) {
    if (litepublisher::$options->group == 'ticket') {
      $admin = tadminmenus::i();
      return $admin->items[$id]['url'] == '/admin/posts/';
    }
    return false;
  }

} //class