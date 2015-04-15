<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpermpassword extends tperm {
  
  protected function create() {
    parent::create();
    $this->adminclass = 'tadminpermpassword';
    $this->data['password'] = '';
    $this->data['login'] = '';
  }
  
  public function getheader($obj) {
    if ($this->password == '') return '';
    return sprintf('<?php %s::i(%d)->auth(); ?>', get_class($this), $this->id);
  }
  
  public function hasperm($obj) {
    return $this->authcookie();
  }
  
  public function getcookiename() {
    return 'permpassword_' .$this->id;
  }
  
  public function setpassword($p) {
    $p = trim($p);
    if ($p == '') return false;
    $this->data['login'] = md5uniq();
    $this->data['password'] = md5($this->login . litepublisher::$secret . $p . litepublisher::$options->solt);
    $this->save();
  }
  
  public function checkpassword($p) {
    if ($this->password != md5($this->login . litepublisher::$secret . $p . litepublisher::$options->solt)) return false;
    $login = md5rand();
    $password = md5($login . litepublisher::$secret . $this->password . litepublisher::$options->solt);
    $cookie = $login . '.' . $password;
    $expired = isset($_POST['remember']) ? time() + 31536000 : time() + 8*3600;
    
    setcookie($this->getcookiename(), $cookie, $expired, litepublisher::$site->subdir . '/', false);
    return true;
  }
  
  public function authcookie() {
    if (litepublisher::$options->group == 'admin') return true;
    $cookiename = $this->getcookiename();
    $cookie = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
    if (($cookie == '') || !strpos($cookie, '.')) return $this->redir();
    list($login, $password) = explode('.', $cookie);
    if ($password == md5($login . litepublisher::$secret . $this->password . litepublisher::$options->solt)) return true;
    return false;
  }
  
  public function auth() {
    if ($this->authcookie()) return true;
    return $this->redir();
  }
  
  public function redir() {
    $url = litepublisher::$site->url . '/check-password.php' . litepublisher::$site->q;
    $url .= "idperm=$this->id&backurl=" . urlencode(litepublisher::$urlmap->url);
    litepublisher::$urlmap->redir($url, 307);
  }
  
}//class