<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class emailauth extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function email_login(array $args) {
    if (!isset($args['email']) || !isset($args['password'])) return $this->error('Invalid data', 403);
    $email = strtolower(trim($args['email']));
    $password = trim($args['password']);
    
    if ($mesg = tadminlogin::autherror($email, $password)) {
      return array(
      'error' => $mesg
      );
    }
    
    $expired = time() + 31536000;
    $cookie = md5uniq();
    litepublisher::$options->setcookies($cookie, $expired);
    
    return array(
    'id' => litepublisher::$options->user,
    'pass' => $cookie,
    'regservice' => 'email',
    'adminflag' => litepublisher::$options->ingroup('admin') ? 'true' : '',
    );
  }
  
  public function email_reg(array $args) {
    if (!litepublisher::$options->usersenabled || !litepublisher::$options->reguser) return array(
    'error' => tlocal::admin('users')->regdisabled
    );
    
    try {
      return tadminreguser ::i()->reguser($args['email'], $args['name']);
    } catch (Exception $e) {
      return array(
      'error' => $e->getMessage()
      );
    }
  }
  
  public function email_lostpass(array $args) {
    try {
      return tadminpassword::i()->restore($args['email']);
    } catch (Exception $e) {
      return array(
      'error' => $e->getMessage()
      );
    }
  }
  
}//class