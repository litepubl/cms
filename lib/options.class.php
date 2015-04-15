<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class toptions extends tevents_storage {
  public $groupnames;
  public $parentgroups;
  public $group;
  public $idgroups;
  protected $_user;
  protected $_admincookie;
  public $gmt;
  public $errorlog;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'options';
    $this->addevents('changed', 'perpagechanged', 'onsave');
    unset($this->cache);
    $this->gmt = 0;
    $this->errorlog = '';
    $this->group = '';
    $this->idgroups = array();
    $this->addmap('groupnames', array());
    $this->addmap('parentgroups', array());
  }
  
  public function afterload() {
    parent::afterload();
    date_default_timezone_set($this->timezone);
    $this->gmt = date('Z');
    if (!defined('dbversion')) define('dbversion', true);
  }
  
  public function savemodified() {
    $result = tstorage::savemodified();
    $this->onsave($result);
    return $result;
  }
  
  public function __set($name, $value) {
    if (in_array($name, $this->eventnames)) {
      $this->addevent($name, $value['class'], $value['func']);
      return true;
    }
    
    if (method_exists($this, $set = 'set' . $name)) {
      $this->$set($value);
      return true;
    }
    
    if (!array_key_exists($name, $this->data)  || ($this->data[$name] != $value)) {
      $this->data[$name] = $value;
      if ($name == 'solt') $this->data['emptyhash'] = $this->hash('');
      $this->save();
      $this->dochanged($name, $value);
    }
    return true;
  }
  
  private function dochanged($name, $value) {
    if ($name == 'perpage') {
      $this->perpagechanged();
      $urlmap = turlmap::i();
      $urlmap->clearcache();
    } elseif ($name == 'cache') {
      $urlmap = turlmap::i();
      $urlmap->clearcache();
    } else {
      $this->changed($name, $value);
    }
  }
  
  public function delete($name) {
    if (array_key_exists($name, $this->data)) {
      unset($this->data[$name]);
      $this->save();
    }
  }
  
  public function getadmincookie() {
    if (is_null($this->_admincookie)) {
      return $this->_admincookie = $this->authenabled && isset($_COOKIE['litepubl_user_flag']) && ($_COOKIE['litepubl_user_flag'] == 'true');
    }
    return $this->_admincookie;
  }
  
  public function setadmincookie($val) {
    $this->_admincookie = $val;
  }
  
  public function getuser() {
    if (is_null($this->_user)) {
      $this->_user = $this->authenabled ? $this->authcookie() : false;
    }
    return $this->_user;
  }
  
  public function setuser($id) {
    $this->_user = $id;
  }
  
  public function authcookie() {
    return $this->authcookies(isset($_COOKIE['litepubl_user_id']) ? (int) $_COOKIE['litepubl_user_id'] : 0, isset($_COOKIE['litepubl_user']) ? (string) $_COOKIE['litepubl_user'] : '');
  }
  
  public function authcookies($iduser, $password) {
    if (!$iduser || !$password) return false;
    $password = $this->hash($password);
    if (    $password == $this->emptyhash) return false;
    if (!$this->finduser($iduser, $password)) return false;
    
    $this->_user = $iduser;
    $this->updategroup();
    return $iduser;
  }
  
  public function finduser($iduser, $cookie) {
    if ($iduser == 1) return $this->compare_cookie($cookie);
    if (!$this->usersenabled)  return false;
    
    $users = tusers::i();
    try {
      $item = $users->getitem($iduser);
    } catch (Exception $e) {
      return false;
    }
    
    if ('hold' == $item['status']) return false;
    return ($cookie == $item['cookie']) && (strtotime($item['expired']) > time());
  }
  
  private function compare_cookie($cookie) {
    return !empty($this->cookiehash) && ($this->cookiehash == $cookie) && ($this->cookieexpired > time());
  }
  
  public function emailexists($email) {
    if (!$email) return false;
    if (!$this->authenabled) return false;
    if ($email == $this->email) return 1;
    if(!$this->usersenabled) return false;
    return tusers::i()->emailexists($email);
  }
  
  public function auth($email, $password) {
    if (!$this->authenabled) return false;
    if (!$email && !$password) return $this->authcookie();
    return $this->authpassword($this->emailexists($email), $password);
  }
  
  public function authpassword($iduser, $password) {
    if (!$iduser) return false;
    if ($iduser == 1) {
      if ($this->data['password'] != $this->hash($password))  return false;
    } else {
      if (!tusers::i()->authpassword($iduser, $password)) return false;
    }
    
    $this->_user = $iduser;
    $this->updategroup();
    return $iduser;
  }
  
  public function updategroup() {
    if ($this->_user == 1) {
      $this->group = 'admin';
      $this->idgroups = array(1);
    } else {
      $user = tusers::i()->getitem($this->_user);
      $this->idgroups = $user['idgroups'];
      $this->group = count($this->idgroups) ? tusergroups::i()->items[$this->idgroups[0]]['name'] : '';
    }
  }
  
  public function can_edit($idauthor) {
    return ($idauthor == $this->user) || ($this->group == 'admin') || ($this->group == 'editor');
  }
  
  public function getpassword() {
    if ($this->user <= 1) return $this->data['password'];
    $users = tusers::i();
    return $users->getvalue($this->user, 'password');
  }
  
  public function changepassword($newpassword) {
    $this->data['password'] = $this->hash($newpassword);
    $this->save();
  }
  
  public function getdbpassword() {
    if (function_exists('mcrypt_encrypt')) {
      return self::decrypt(    $this->data['dbconfig']['password'], $this->solt . litepublisher::$secret);
    } else {
      return str_rot13(base64_decode($this->data['dbconfig']['password']));
    }
  }
  
  public function setdbpassword($password) {
    if (function_exists('mcrypt_encrypt')) {
      $this->data['dbconfig']['password'] = self::encrypt($password, $this->solt . litepublisher::$secret);
    } else {
      $this->data['dbconfig']['password'] = base64_encode(str_rot13 ($password));
    }
    
    $this->save();
  }
  
  public function logout() {
    $this->setcookies('', 0);
  }
  
  public function setcookie($name, $value, $expired) {
    setcookie($name, $value, $expired,  litepublisher::$site->subdir . '/', false, '', $this->securecookie);
  }
  
  public function setcookies($cookie, $expired) {
    $this->setcookie('litepubl_user_id', $cookie ? $this->_user : '', $expired);
    $this->setcookie('litepubl_user', $cookie, $expired);
    $this->setcookie('litepubl_user_flag', $cookie && ('admin' == $this->group) ? 'true' : '', $expired);
    
    if ($this->_user == 1) {
      $this->save_cookie($cookie, $expired);
    } else if ($this->_user) {
      tusers::i()->setcookie($this->_user, $cookie, $expired);
    }
  }
  
  public function Getinstalled() {
    return isset($this->data['email']);
  }
  
  public function settimezone($value) {
    if(!isset($this->data['timezone']) || ($this->timezone != $value)) {
      $this->data['timezone'] = $value;
      $this->save();
      date_default_timezone_set($this->timezone);
      $this->gmt = date('Z');
    }
  }
  
  public function save_cookie($cookie, $expired) {
    $this->data['cookiehash'] = $cookie ? $this->hash($cookie) : '';
    $this->cookieexpired = $expired;
    $this->save();
  }
  
  public function hash($s) {
    return basemd5((string) $s . $this->solt . litepublisher::$secret);
  }
  
  public function ingroup($groupname) {
    //admin has all rights
    if ($this->user == 1) return true;
    if (in_array($this->groupnames['admin'], $this->idgroups)) return true;
    if (!$groupname) return true;
    $groupname = trim($groupname);
    if ($groupname == 'admin') return false;
    if (!isset($this->groupnames[$groupname])) $this->error(sprintf('The "%s" group not found', $groupname));
    $idgroup = $this->groupnames[$groupname];
    return in_array($idgroup, $this->idgroups);
  }
  
  public function ingroups(array $idgroups) {
    if ($this->ingroup('admin')) return true;
    return count(array_intersect($this->idgroups, $idgroups));
  }
  
  public function hasgroup($groupname) {
    if ($this->ingroup($groupname)) return true;
    // if group is children of user groups
    $idgroup = $this->groupnames[$groupname];
    if (!isset($this->parentgroups[$idgroup])) return false;
    return count(array_intersect($this->idgroups, $this->parentgroups[$idgroup]));
  }
  
  public function handexception($e) {
    $log = "Caught exception:\r\n" . $e->getMessage() . "\r\n";
    $trace = $e->getTrace();
    foreach ($trace as $i => $item) {
      if (isset($item['line'])) {
        $log .= sprintf('#%d %d %s ', $i, $item['line'], $item['file']);
      }
      
      if (isset($item['class'])) {
        $log .= $item['class'] . $item['type'] . $item['function'];
      } else {
        $log .= $item['function'];
      }
      
      if (isset($item['args']) && count($item['args'])) {
        $args = array();
        foreach ($item['args'] as $arg) {
          $args[] = self::var_export($arg);
        }
        
        $log .= "\n";
        $log .= implode(', ', $args);
      }
      
      $log .= "\n";
    }
    
    $log = str_replace(litepublisher::$paths->home, '', $log);
    $this->errorlog .= str_replace("\n", "<br />\n", htmlspecialchars($log));
    tfiler::log($log, 'exceptions.log');
    
    if (!(litepublisher::$debug || $this->echoexception || $this->admincookie || litepublisher::$urlmap->adminpanel)) {
      tfiler::log($log, 'exceptionsmail.log');
    }
  }
  
  public function trace($msg) {
    try {
      throw new Exception($msg);
    } catch (Exception $e) {
      $this->handexception($e);
    }
  }
  
  public function showerrors() {
    if (!empty($this->errorlog) && (litepublisher::$debug || $this->echoexception || $this->admincookie || litepublisher::$urlmap->adminpanel)) {
      echo $this->errorlog;
    }
  }
  
  public static function var_export(&$v) {
    switch(gettype($v)) {
      case 'string':
      return "'$v'";
      
      case 'object':
      return get_class($v);
      
      case 'boolean':
      return $v ? 'true' : 'false';
      
      case 'integer':
      case 'double':
      case 'float':
      return $v;
      
      case 'array':
      $result = "array (\n";
      foreach ($v as $k => $item) {
        $s = self::var_export($item);
        $result .= "$k = $s;\n";
      }
      $result .= ")\n";
      return $result;
      
      default:
      return gettype($v);
    }
  }
  
}//class