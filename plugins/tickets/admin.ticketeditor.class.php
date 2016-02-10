<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tticketeditor extends tposteditor {

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function gettitle() {
    if ($this->idpost == 0) {
      return parent::gettitle();
    } else {
      return tlocal::admin('tickets')->editor;
    }
  }

  public function canrequest() {
    if ($s = parent::canrequest()) return $s;
    $this->basename = 'tickets';
    if ($this->idpost > 0) {
      $ticket = tticket::i($this->idpost);
      if ((litepublisher::$options->group == 'ticket') && (litepublisher::$options->user != $ticket->author)) return 403;
    }
  }

public function gettabs($post = null) {
$post = $this->getvarpost($post);
$args = new targs();
$this->getargstab($post, $args);

    $lang = tlocal::admin('tickets');
    $lang->addsearch('ticket', 'tickets', 'editor');

$admintheme = $this->admintheme;
$tabs = new tabs($admintheme);
// #tabs for posteditor.js
$tabs->id = 'tabs';

$tb = new tablebuilder($admintheme);
$tabs->add($lang->ticket, $tb->inputs(array(
'combo' => 'category',
'combo' => 'state',
'combo' => 'prio',
'text' => 'version',
'text' => 'os',
)));

$tabs->ajax(
return $atmintheme->parsearg($tabs->get(), $args);
}

  public function getargstab(tpost $ticket, targs $args) {
    $args->ajax = $this->getajaxlink($ticket->id);
    $args->fixed = $ticket->state == 'fixed';

$lang = tlocal::admin('tickets');
    $tickets = ttickets::i();
    $args->category = static::getcombocategories($tickets->cats, count($ticket->categories) ? $ticket->categories[0] : $tickets->cats[0]);
$args->version = $ticket->version;
$args->os = $ticket->os;

    $states = array();
    foreach (array(
      'fixed',
      'opened',
      'wontfix',
      'invalid',
      'duplicate',
      'reassign'
    ) as $state) {
      $states[$state] = $lang->$state;
    }

    $args->state = tadminhtml::array2combo($states, $ticket->state);

    $prio = array();
    foreach (array(
      'trivial',
      'minor',
      'major',
      'critical',
      'blocker'
    ) as $p) {
      $prio[$p] = $lang->$p;
    }

    $args->prio = tadminhtml::array2combo($prio, $ticket->prio);
}

  public function gettext() {
    $args->code = $html->getinput('editor', 'code', tadminhtml::specchars($ticket->code) , $lang->codetext);
}

public function newpost() {
return new tticket();
}

  public function processform() {
    /* dumpvar($_POST);
    return;






    */
    extract($_POST, EXTR_SKIP);
    $tickets = ttickets::i();
    $this->basename = 'tickets';
    $html = $this->html;

    // check spam
    if ($id == 0) {
      $newstatus = 'published';
      if (litepublisher::$options->group == 'ticket') {
        $hold = $tickets->db->getcount('status = \'draft\' and author = ' . litepublisher::$options->user);
        $approved = $tickets->db->getcount('status = \'published\' and author = ' . litepublisher::$options->user);
        if ($approved < 3) {
          if ($hold - $approved >= 2) return $html->h4->noapproved;
          $newstatus = 'draft';
        }
      }
    }
    if (empty($title)) {
      $lang = tlocal::i('editor');
      return $html->h4->emptytitle;
    }
    $ticket = tticket::i((int)$id);
    $ticket->title = $title;
    $ticket->categories = array(
      (int)$combocat
    );
    if (isset($tags)) $ticket->tagnames = $tags;
    if ($ticket->author == 0) $ticket->author = litepublisher::$options->user;
    if (isset($files)) {
      $files = trim($files);
      $ticket->files = $files == '' ? array() : explode(',', $files);
    }

    $ticket->content = tcontentfilter::quote(htmlspecialchars($raw));
    $ticket->code = $code;
    $ticket->prio = $prio;
    $ticket->set_state($state);
    $ticket->version = $version;
    $ticket->os = $os;
    //if (litepublisher::$options->group != 'ticket') $ticket->state = $state;
    if ($id == 0) {
      $ticket->status = $newstatus;
      $ticket->categories = array(
        (int)$combocat
      );
      $ticket->closed = time();
      $id = $tickets->add($ticket);
      $_GET['id'] = $id;
      $_POST['id'] = $id;
      $this->idpost = $id;
    } else {
      $tickets->edit($ticket);
    }

    return $html->h4->successedit;
  }

} //class
