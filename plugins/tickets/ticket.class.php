<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tticket extends tpost {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getchildtable() {
    return 'tickets';
  }
  
  public static function selectitems(array $items) {
    return self::select_child_items('tickets', $items);
  }
  
  protected function create() {
    parent::create();
    $this->data['childdata'] = &$this->childdata;
    $this->childdata = array(
    'id' => 0,
    //'type' => 'bug',
    'state'  => 'opened',
    'prio' => 'major',
    'assignto' => 0,
    'closed' => '',
    'version'=> litepublisher::$options->version,
    'poll' => 0,
    'os'=> '*',
    'reproduced' => false,
    'code' => ''
    );
  }
  
  public function beforedb() {
    if ($this->childdata['closed'] == '') $this->childdata['closed'] = sqldate();
  }
  
  public function afterdb() {
    $this->childdata['reproduced'] = $this->childdata['reproduced'] == '1';
  }
  
  protected function getclosed() {
    return strtotime($this->childdata['closed']);
  }
  
  protected function setclosed($value) {
    $this->childdata['closed'] = is_int($value) ? sqldate($value) : $value;
  }
  
  protected function getcontentpage($page) {
    $result = '';
    if ($this->poll > 0) {
      $polls = tpolls::i();
      $result .= $polls->gethtml($this->poll, true);
    }
    
    $result .= parent::getcontentpage($page);
    return $result;
  }
  
  public function updatefiltered() {
    $result = $this->getticketcontent();
    $filter = tcontentfilter::i();
    $filter->filterpost($this,$this->rawcontent);
    $result .= $this->filtered;
    if (!empty($this->childdata['code'])) {
      $lang = tlocal::i('ticket');
      $result .= sprintf('<h2>%s</h2>', $lang->code);
      $result .= highlight_string($this->code, true);
    }
    $this->filtered = $result;
  }
  
  public function getticketcontent() {
    $lang = tlocal::i('ticket');
    $args = targs::i();
    foreach (array('state', 'prio') as $prop) {
      $value = $this->$prop;
      $args->$prop = $lang->$value;
    }
    $args->reproduced = $this->reproduced ? $lang->yesword : $lang->noword;
    $args->assignto = $this->assigntoname;
    $args->author = $this->authorlink;
    
    ttheme::$vars['ticket'] = $this;
    $theme = $this->theme;
    $tml = file_get_contents($this->resource . 'ticket.tml');
    return $theme->parsearg($tml, $args);
  }
  
  protected function getassigntoname() {
    return $this->getusername($this->assignto, true);
  }
  
  public function closepoll() {
    $polls = tpolls::i();
    $polls->db->setvalue($this->poll, 'status', 'closed');
  }
  
  public static function getresource() {
    return litepublisher::$paths->plugins . 'tickets'  . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  }
  
  public function getschemalink() {
    return 'ticket';
  }
  
  
  public function set_state($state) {
    $old = $this->state;
    if ($state == $old) return;
    $this->childdata['state'] = $state;
    if ($this->id == 0) return;
    
    $lang = tlocal::i('ticket');
    $content = sprintf($lang->statechanged, $lang->$old, $lang->$state);
    
    $this->comments->add($this->id, ttickets::i()->idcomauthor,  $content, 'approved', '');
    //$this->commentscount = $this->comments->db->getcount("post = $this->id and status = 'approved'");
  }
  
}//class