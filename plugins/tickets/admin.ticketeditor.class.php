<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tticketeditor extends tposteditor {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    $template = ttemplate::i();
    $result .= $template->getready('
    //alert($("#tabs").parent().parent().html());
    //$("textarea:first").val($("#tabs").parent().parent().html());
  $("#tabs, #contenttabs").tabs({ beforeLoad: litepubl.uibefore});');
    return $result;
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
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
  
  public function getcontent() {
    $result = '';
    $this->basename = 'tickets';
    $ticket = tticket::i($this->idpost);
    ttheme::$vars['ticket'] = $ticket;
    ttheme::$vars['post'] = $ticket;
    $args = new targs();
    $args->id = $this->idpost;
    $args->title = tcontentfilter::unescape($ticket->title);
    $args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$ticket->id&get");
    $ajaxeditor = tajaxposteditor ::i();
    $args->raw = $ajaxeditor->geteditor('raw', $ticket->rawcontent, true);
    
    $html = $this->inihtml('tickets');
    $lang = tlocal::admin('tickets');
    $lang->ini['tickets'] = $lang->ini['ticket'] + $lang->ini['tickets'];
    
    $args->code = $html->getinput('editor', 'code', tadminhtml::specchars($ticket->code), $lang->codetext);
    
    $args->fixed = $ticket->state == 'fixed';
    
    $tickets = ttickets::i();
    $args->catcombo = tposteditor::getcombocategories($tickets->cats, count($ticket->categories) ? $ticket->categories[0] : $tickets->cats[0]);
    
    $states =array();
    foreach (array('fixed', 'opened', 'wontfix', 'invalid', 'duplicate', 'reassign') as $state) {
      $states[$state] = $lang->$state;
    }
    $args->statecombo= $html->array2combo($states, $ticket->state);
    
    $prio = array();
    foreach (array('trivial', 'minor', 'major', 'critical', 'blocker') as $p) {
      $prio[$p] = $lang->$p;
    }
    $args->priocombo = $html->array2combo($prio, $ticket->prio);
    
    if ($ticket->id > 0) $result .= $html->headeditor ();
    $result .= $html->form($args);
    $result = $html->fixquote($result);
    return $result;
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
        $hold = $tickets->db->getcount('status = \'draft\' and author = '. litepublisher::$options->user);
        $approved = $tickets->db->getcount('status = \'published\' and author = '. litepublisher::$options->user);
        if ($approved < 3) {
          if ($hold - $approved >= 2) return $html->h4->noapproved;
          $newstatus = 'draft';
        }
      }
    }
    if (empty($title)) {
      $lang =tlocal::i('editor');
      return $html->h4->emptytitle;
    }
    $ticket = tticket::i((int)$id);
    $ticket->title = $title;
    $ticket->categories = array((int) $combocat);
    if (isset($tags)) $ticket->tagnames = $tags;
    if ($ticket->author == 0) $ticket->author = litepublisher::$options->user;
    if (isset($files))  {
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
      $ticket->categories = array((int) $combocat);
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
  
}//class