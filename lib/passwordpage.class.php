<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpasswordpage extends tevents_itemplate implements itemplate {
  public $perm;
  private $formresult;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'passwordpage';
    $this->formresult = '';
    $this->data['form'] = '';
    $this->data['title'] = '';
  }
  
  private function checkspam($s) {
    if  (!($s = @base64_decode($s))) return false;
    $sign = 'megaspamer';
    if (!strbegin($s, $sign)) return false;
    $timekey = (int) substr($s, strlen($sign));
    return time() < $timekey;
  }
  
  public function request($arg) {
    $this->cache = false;
    if (!tguard::post()) return;
    
    $antispam = isset($_POST['antispam']) ? $_POST['antispam'] : '';
    if (!$this->checkspam($antispam))          return 403;
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    if ($password == '') return;
    if (!isset($this->perm)) {
      $idperm = isset($_GET['idperm']) ? (int) $_GET['idperm'] : 0;
      $perms = tperms::i();
      if (!$perms->itemexists($idperm)) return 403;
      $this->perm = tperm::i($idperm);
    }
    
    $backurl = isset($_GET['backurl']) ? $_GET['backurl'] : '';
    
    if ($this->perm->checkpassword($password)) {
      if ($backurl != '') litepublisher::$urlmap->redir($backurl);
    } else {
      $this->formresult = $this->invalidpassword;
    }
  }
  
  public function gettitle() {
    return $this->data['title'];
  }
  
  public function getcont() {
    $result = $this->formresult == '' ? '' : sprintf('<h4>%s</h4>', $this->formresult);
    $args = new targs();
    $args->antispam = base64_encode('megaspamer' . strtotime ("+1 hour"));
    $args->remember = isset($_POST['remember']);
    $result .= $this->view->theme->parsearg($this->form, $args);
    return $result;
  }
  
}// class