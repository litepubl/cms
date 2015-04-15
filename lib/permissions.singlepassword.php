<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsinglepassword extends tperm {
  private $password;
  private $checked;
  
  public function getheader($obj) {
    if (isset($obj->password) && ($p = $obj->password)) {
      return sprintf('<?php if (!%s::auth(%d, \'%s\')) return; ?>', get_class($this), $this->id, self::encryptpassword($p));
    }
  }
  
  public function hasperm($obj) {
    if (isset($obj->password) && ($p = $obj->password)) {
      return self::authcookie(self::encryptpassword($p));
    }
    return true;
  }
  
  public static function encryptpassword($p) {
    return md5(litepublisher::$urlmap->itemrequested['id'] . litepublisher::$secret . $p . litepublisher::$options->solt);
  }
  
  public static function getcookiename() {
    return 'singlepwd_' . litepublisher::$urlmap->itemrequested['id'];
  }
  
  public function checkpassword($p) {
    if ($this->password != self::encryptpassword($p)) return false;
    $login = md5rand();
    $password = md5($login . litepublisher::$secret . $this->password. litepublisher::$options->solt);
    $cookie = $login . '.' . $password;
    $expired = isset($_POST['remember']) ? time() + 31536000 : time() + 8*3600;
    
    setcookie(self::getcookiename(), $cookie, $expired, litepublisher::$site->subdir . '/', false);
    $this->checked = true;
    return true;
  }
  
  public static function authcookie($p) {
    if (litepublisher::$options->group == 'admin') return true;
    $cookiename = self::getcookiename();
    $cookie = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
    if (($cookie != '') && strpos($cookie, '.')) {
      list($login, $password) = explode('.', $cookie);
      if ($password == md5($login . litepublisher::$secret . $p. litepublisher::$options->solt)) return true;
    }
    return false;
  }
  
  public static function auth($id, $p) {
    if (self::authcookie($p)) return true;
    $self = self::i($id);
    return $self->getform($p);
  }
  
  public function getform($p) {
    $this->password = $p;
    $page = tpasswordpage::i();
    $page->perm = $this;
    $result = $page->request(null);
    if ($this->checked) return true;
    
    switch ($result) {
      case 404: return litepublisher::$urlmap->notfound404();
      case 403: return litepublisher::$urlmap->forbidden();
    }
    
    $html  = ttemplate::i()->request($page);
    eval('?>'. $html);
    return false;
  }
  
}//class