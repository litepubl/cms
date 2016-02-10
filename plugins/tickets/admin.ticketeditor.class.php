<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tticketeditor extends tposteditor {
private $newstatus;

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

public function gettext($post = null) {
$post = $this->getvarpost($post);
$admintheme = $this->admintheme;
$lang = tlocal::admin('tickets');
$tabs = new tabs($admintheme);
$tabs->add($lang->text, '[editor=raw]');
$tabs->add($lang->codetext, '[editor=code]');

$args = new targs();
$args->raw = $post->rawcontent;
    $args->code = $post->code;

return $admintheme->parsearg($tabs->get(), $args);
}

public function newpost() {
return new tticket();
}

public function canprocess() {
if ($error = parent::canprocess()) {
return $error;
}

    // check spam
    $tickets = ttickets::i();
    if ($id == 0) {
      $this->newstatus = 'published';
      if (litepublisher::$options->group == 'ticket') {
        $hold = $tickets->db->getcount('status = \'draft\' and author = ' . litepublisher::$options->user);
        $approved = $tickets->db->getcount('status = \'published\' and author = ' . litepublisher::$options->user);
        if ($approved < 3) {
          if ($hold - $approved >= 2) {
return tlocal::admin('tickets')->noapproved;
}

          $this->newstatus = 'draft';
        }
      }
    }
}

  public function processtab(tpost $ticket) {
    extract($_POST, EXTR_SKIP);

    $ticket->title = $title;
    $ticket->prio = $prio;
    $ticket->set_state($state);
    $ticket->version = $version;
    $ticket->os = $os;
    $ticket->categories = array(
      (int)$category
    );

    if (isset($tags)) {
$ticket->tagnames = $tags;
}

    if ($ticket->author == 0) {
$ticket->author = litepublisher::$options->user;
}

    if ($ticket->id == 0) {
      $ticket->status = $this->newstatus;
      $ticket->closed = time();
}

    $ticket->content = tcontentfilter::quote(htmlspecialchars($raw));
    $ticket->code = $code;
}

}//class