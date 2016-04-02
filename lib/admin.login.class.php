<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

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
    if (!litepubl::$options->authcookie()) return litepubl::$urlmap->redir('/admin/login/');
  }

  private function logout() {
    litepubl::$options->logout();
    setcookie('backurl', '', 0, litepubl::$site->subdir, false);
    litepubl::$urlmap->nocache();
    return litepubl::$urlmap->redir('/admin/login/');
  }

  //return error string message if not logged
  public static function autherror($email, $password) {
    tlocal::admin();
    if (empty($email) || empty($password)) return tlocal::get('login', 'empty');

    $iduser = litepubl::$options->emailexists($email);
    if (!$iduser) {
      if (static::confirm_reg($email, $password)) return;
      return tlocal::get('login', 'unknownemail');
    }

    if (litepubl::$options->authpassword($iduser, $password)) return;
    if (static::confirm_restore($email, $password)) return;

    //check if password is empty and neet to restore password
    if ($iduser == 1) {
      if (!litepubl::$options->password) return tlocal::get('login', 'torestorepass');
    } else {
      if (!tusers::i()->getpassword($iduser)) return tlocal::get('login', 'torestorepass');
    }

    return tlocal::get('login', 'error');
  }

  public function request($arg) {
    if ($arg == 'out') return $this->logout($arg);
    parent::request($arg);
    $this->section = 'login';

    if (!isset($_POST['email']) || !isset($_POST['password'])) return turlmap::nocache();
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($mesg = static::autherror($email, $password)) {
      $this->formresult = $this->html->h4red($mesg);
      return turlmap::nocache();;
    }

    $expired = isset($_POST['remember']) ? time() + 31536000 : time() + 8 * 3600;
    $cookie = md5uniq();
    litepubl::$options->setcookies($cookie, $expired);
    litepubl::$options->setcookie('litepubl_regservice', 'email', $expired);

    $url = !empty($_GET['backurl']) ? $_GET['backurl'] : (!empty($_GET['amp;backurl']) ? $_GET['amp;backurl'] : (isset($_COOKIE['backurl']) ? $_COOKIE['backurl'] : ''));

    if ($url && strbegin($url, litepubl::$site->url)) $url = substr($url, strlen(litepubl::$site->url));
    if ($url && (strbegin($url, '/admin/login/') || strbegin($url, '/admin/password/'))) $url = false;

    if (!$url) {
      $url = '/admin/';
      if (litepubl::$options->group != 'admin') {
        $groups = tusergroups::i();
        $url = $groups->gethome(litepubl::$options->group);
      }
    }

    litepubl::$options->setcookie('backurl', '', 0);
    turlmap::nocache();
    return litepubl::$urlmap->redir($url);
  }

  public function createform() {
    $result = $this->widget;
    $theme = tview::getview($this)->theme;
    $args = new targs();

    if (litepubl::$options->usersenabled && litepubl::$options->reguser) {
      $lang = tlocal::admin('users');
      $form = new adminform($args);
      $form->title = $lang->regform;
      $form->action = '$site.url/admin/reguser/{$site.q}backurl=';
      $form->body = $theme->getinput('email', 'email', '', 'E-Mail');
      $form->body.= $theme->getinput('text', 'name', '', $lang->name);
      $form->submit = 'signup';
      $result.= $form->get();
    }

    $lang = tlocal::admin('login');
    $form = new adminform($args);
    $form->title = $lang->emailpass;
    $form->body = $theme->getinput('email', 'email', '$email', 'E-Mail');
    $form->body.= $theme->getinput('password', 'password', '', $lang->password);
    $form->body.= $theme->getinput('checkbox', 'remember', '$remember', $lang->remember);
    $form->submit = 'log_in';
    $result.= $form->gettml();

    $form = new adminform($args);
    $form->title = $lang->lostpass;
    $form->action = '$site.url/admin/password/';
    $form->target = '_blank';
    $form->inline = true;
    $form->body = $theme->getinput('email', 'email', '', 'E-Mail');
    $form->submit = 'sendpass';
    $result.= $form->get();

    return $result;
  }

  public function getcontent() {
    $result = $this->getform();

    $args = new targs();
    $args->email = isset($_POST['email']) ? trim(strip_tags($_POST['email'])) : '';
    $args->remember = isset($_POST['remember']);
    $result = tview::getview($this)->theme->parsearg($result, $args);

    $result = str_replace('&amp;backurl=', '&backurl=', $result);
    if (!empty($_GET['backurl'])) {
      $result = str_replace('backurl=', 'backurl=' . urlencode($_GET['backurl']) , $result);
      //support ulogin
      $result = str_replace('backurl%3D', 'backurl%3D' . urlencode(urlencode($_GET['backurl'])) , $result);
    } else {
      $result = str_replace('&backurl=', '', $result);
      $result = str_replace('backurl=', '', $result);
      //support ulogin
      $result = str_replace('%3Fbackurl%3D', '', $result);
    }

    $this->callevent('oncontent', array(&$result
    ));
    return $result;
  }

  public static function confirm_reg($email, $password) {
    if (!litepubl::$options->usersenabled || !litepubl::$options->reguser) return false;

    tsession::start('reguser-' . md5(litepubl::$options->hash($email)));
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
      litepubl::$options->user = $id;
      litepubl::$options->updategroup();
    }

    return $id;
  }

  public static function confirm_restore($email, $password) {
    tsession::start('password-restore-' . md5(litepubl::$options->hash($email)));
    if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($password != $_SESSION['password'])) {
      if (isset($_SESSION['email'])) {
        session_write_close();
      } else {
        session_destroy();
      }
      return false;
    }

    session_destroy();
    if ($email == strtolower(trim(litepubl::$options->email))) {
      litepubl::$options->changepassword($password);
      return 1;
    } else {
      $users = tusers::i();
      if ($id = $users->emailexists($email)) $users->changepassword($id, $password);
      return $id;
    }
  }

} //class