<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlogin extends tadminform {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'admin.loginform';
    $this->addevents('oncontent');
    $this->data['widget'] = '';
  }
  
  public function auth() {
    if ($s = tguard::checkattack()) return $s;
    if (!litepublisher::$options->authcookie()) return litepublisher::$urlmap->redir('/admin/login/');
  }
  
  private function logout() {
    litepublisher::$options->logout();
    setcookie('backurl', '', 0, litepublisher::$site->subdir, false);
    litepublisher::$urlmap->nocache();
    return litepublisher::$urlmap->redir('/admin/login/');
  }
  
  //return error string message if not logged
  public static function autherror($email, $password) {
    tlocal::admin();
    if (empty($email) || empty($password)) return tlocal::get('login', 'empty');
    
    $iduser = litepublisher::$options->emailexists($email);
    if (!$iduser) {
      if (self::confirm_reg($email, $password)) return;
      return tlocal::get('login', 'unknownemail');
    }
    
    if (litepublisher::$options->authpassword($iduser, $password)) return;
    if (self::confirm_restore($email, $password)) return;
    
    //check if password is empty and neet to restore password
    if ($iduser == 1) {
      if (!litepublisher::$options->password)  return tlocal::get('login', 'torestorepass');
    } else {
      if (!tusers::i()->getpassword($iduser)) return tlocal::get('login', 'torestorepass');
    }
    
    return tlocal::get('login', 'error');
  }
  
  public function request($arg) {
    if ($arg == 'out')   return $this->logout($arg);
    parent::request($arg);
    $this->section = 'login';
    
    if (!isset($_POST['email']) || !isset($_POST['password'])) return     turlmap::nocache();
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if ($mesg = self::autherror($email, $password)) {
      $this->formresult = $this->html->h4red($mesg);
      return     turlmap::nocache();;
    }
    
    $expired = isset($_POST['remember']) ? time() + 31536000 : time() + 8*3600;
    $cookie = md5uniq();
    litepublisher::$options->setcookies($cookie, $expired);
    litepublisher::$options->setcookie('litepubl_regservice', 'email', $expired);
    
    $url = !empty($_GET['backurl']) ? $_GET['backurl'] : (!empty($_GET['amp;backurl']) ? $_GET['amp;backurl'] :  (isset($_COOKIE['backurl']) ? $_COOKIE['backurl'] : ''));
    
    if ($url && strbegin($url, litepublisher::$site->url)) $url = substr($url, strlen(litepublisher::$site->url));
    if ($url && (strbegin($url, '/admin/login/') || strbegin($url, '/admin/password/'))) $url = false;
    
    if (!$url) {
      $url = '/admin/';
      if (litepublisher::$options->group != 'admin') {
        $groups = tusergroups::i();
        $url = $groups->gethome(litepublisher::$options->group);
      }
    }
    
    litepublisher::$options->setcookie('backurl', '', 0);
    turlmap::nocache();
    return litepublisher::$urlmap->redir($url);
  }
  
  public function getcontent() {
    $result = $this->widget;
    $result = str_replace('&amp;backurl=', '&backurl=', $result);
    if (!empty($_GET['backurl'])) {
      $result = str_replace('backurl=', 'backurl=' . urlencode($_GET['backurl']), $result);
      //support ulogin
      $result = str_replace('backurl%3D', 'backurl%3D' . urlencode(urlencode($_GET['backurl'])), $result);
    } else {
      $result = str_replace('&backurl=', '', $result);
      $result = str_replace('backurl=', '', $result);
      //support ulogin
      $result = str_replace('%3Fbackurl%3D', '', $result);
    }
    
    $html = $this->html;
    $args = new targs();
    if (litepublisher::$options->usersenabled && litepublisher::$options->reguser) {
      $lang = tlocal::admin('users');
      $form = new  adminform($args);
      $form->action = litepublisher::$site->url . '/admin/reguser/';
      if (!empty($_GET['backurl'])) {
        $form->action .= '?backurl=' . urlencode($_GET['backurl']);
      }
      $form->title = $lang->regform;
      $args->email = '';
      $args->name = '';
      $form->items = '[text=email] [text=name]';
      $form->submit = 'signup';
      //fix id text-email
      $result .= str_replace('text-email', 'reg-email', $form->get());
    }
    
    $lang = tlocal::admin('login');
    $form = new adminform($args);
    $form->title = $lang->emailpass;
    $args->email = !empty($_POST['email']) ? strip_tags($_POST['email']) : '';
    $args->password = !empty($_POST['password']) ? strip_tags($_POST['password']) : '';
    $args->remember = isset($_POST['remember']);
    $form->items = '[text=email]
    [password=password]
    [checkbox=remember]';
    
    $form->submit = 'log_in';
    $result .= $form->get();
    
    $form = new adminform($args);
    $form->title = $lang->lostpass;
    $form->action = '$site.url/admin/password/';
    $form->target = '_blank';
    $form->inline = true;
    // double "text-email" input id
    $form->items = str_replace('text-email', 'lostpass-email',
    $html->getinput('text', 'email', '', 'E-Mail'));
    $form->submit = 'sendpass';
    $result .= $form->get();
    $this->callevent('oncontent', array(&$result));
    return $result;
  }
  
  public static function confirm_reg($email, $password) {
    if (!litepublisher::$options->usersenabled || !litepublisher::$options->reguser) return false;
    
    tsession::start('reguser-' . md5(litepublisher::$options->hash($email)));
    if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($password != $_SESSION['password'])) {
      if (isset($_SESSION['email'])) {
        session_write_close();
      } else {
        session_destroy();
      }
      return false;
    }
    
    $users = tusers::i();
    $id = $users->add(array(
    'password' => $password,
    'name' => $_SESSION['name'],
    'email' => $email
    ));
    
    session_destroy();
    
    if ($id) {
      litepublisher::$options->user = $id;
      litepublisher::$options->updategroup();
    }
    
    return $id;
  }
  
  public static function confirm_restore($email, $password) {
    tsession::start('password-restore-' .md5(litepublisher::$options->hash($email)));
    if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($password != $_SESSION['password'])) {
      if (isset($_SESSION['email'])) {
        session_write_close();
      } else {
        session_destroy();
      }
      return false;
    }
    
    session_destroy();
    if ($email == strtolower(trim(litepublisher::$options->email))) {
      litepublisher::$options->changepassword($password);
      return 1;
    } else {
      $users = tusers::i();
      if ($id = $users->emailexists($email)) $users->changepassword($id, $password);
      return $id;
    }
  }
  
}//class