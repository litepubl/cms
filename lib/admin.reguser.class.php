<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tadminreguser extends tadminform {
  private $regstatus;
  private $backurl;

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->basename = 'admin.reguser';
    $this->addevents('oncontent');
    $this->data['widget'] = '';
    $this->section = 'users';
    $this->regstatus = false;
  }

  public function gettitle() {
    return tlocal::get('users', 'adduser');
  }

  public function getlogged() {
    return litepubl::$options->authcookie();
  }

  public function request($arg) {
    if (!litepubl::$options->usersenabled || !litepubl::$options->reguser) return 403;
    parent::request($arg);

    if (!empty($_GET['confirm'])) {
      $confirm = $_GET['confirm'];
      $email = $_GET['email'];
      tsession::start('reguser-' . md5(litepubl::$options->hash($email)));
      if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($confirm != $_SESSION['confirm'])) {
        if (!isset($_SESSION['email'])) session_destroy();
        $this->regstatus = 'error';
        return;
      }

      $this->backurl = $_SESSION['backurl'];

      $users = tusers::i();
      $id = $users->add(array(
        'password' => $_SESSION['password'],
        'name' => $_SESSION['name'],
        'email' => $_SESSION['email']
      ));

      session_destroy();
      if ($id) {
        $this->regstatus = 'ok';
        $expired = time() + 31536000;
        $cookie = md5uniq();
        litepubl::$options->user = $id;
        litepubl::$options->updategroup();
        litepubl::$options->setcookies($cookie, $expired);
      } else {
        $this->regstatus = 'error';
      }
    }
  }

  public function getcontent() {
    $result = '';
    $view = tview::getview($this);
    $theme = $view->theme;
    $lang = tlocal::admin('users');

    if ($this->logged) {
      return $view->admintheme->geterr($lang->logged . ' ' . $theme->link('/admin/', $lang->adminpanel));
    }

    if ($this->regstatus) {
      switch ($this->regstatus) {
        case 'ok':
          $backurl = $this->backurl;
          if (!$backurl) $backurl = tusergroups::i()->gethome(litepubl::$options->group);
          if (!strbegin($backurl, 'http')) $backurl = litepubl::$site->url . $backurl;
          return $theme->h($lang->successreg . ' ' . $theme->link($backurl, $lang->continue));

        case 'mail':
          return $theme->h($lang->waitconfirm);

        case 'error':
          $result.= $theme->h($lang->invalidregdata);
        }
      }

      $args = new targs();
      $args->email = isset($_POST['email']) ? $_POST['email'] : '';
      $args->name = isset($_POST['name']) ? $_POST['name'] : '';
      $args->action = litepubl::$site->url . '/admin/reguser/' . (!empty($_GET['backurl']) ? '?backurl=' : '');
      $result.= $theme->parsearg($this->getform() , $args);

      if (!empty($_GET['backurl'])) {
        //normalize
        $result = str_replace('&amp;backurl=', '&backurl=', $result);
        $result = str_replace('backurl=', 'backurl=' . urlencode($_GET['backurl']) , $result);
        $result = str_replace('backurl%3D', 'backurl%3D' . urlencode(urlencode($_GET['backurl'])) , $result);
      }

      $this->callevent('oncontent', array(&$result
      ));
      return $result;
  }

  public function createform() {
    $lang = tlocal::i('users');
    $theme = tview::getview($this)->theme;

    $form = new adminform();
    $form->title = $lang->regform;
    $form->action = '$action';
    $form->body = $theme->getinput('email', 'email', '$email', 'E-Mail');
    $form->body.= $theme->getinput('text', 'name', '$name', $lang->name);
    $form->submit = 'signup';

    $result = $form->gettml();
    $result.= $this->widget;
    return $result;
  }

  public function processform() {
    $this->regstatus = 'error';
    try {
      if ($this->reguser($_POST['email'], $_POST['name'])) $this->regstatus = 'mail';
    }
    catch(Exception $e) {
      return sprintf('<h4 class="red">%s</h4>', $e->getMessage());
    }
  }

  public function reguser($email, $name) {
    $email = strtolower(trim($email));
    if (!tcontentfilter::ValidateEmail($email)) return $this->error(tlocal::get('comment', 'invalidemail'));

    if (substr_count($email, '.', 0, strpos($email, '@')) > 2) return $this->error(tlocal::get('comment', 'invalidemail'));

    $users = tusers::i();
    if ($id = $users->emailexists($email)) {
      if ('comuser' != $users->getvalue($id, 'status')) return $this->error(tlocal::i()->invalidregdata);
    }

    tsession::start('reguser-' . md5(litepubl::$options->hash($email)));
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;
    $confirm = md5rand();
    $_SESSION['confirm'] = $confirm;
    $password = md5uniq();
    $_SESSION['password'] = $password;
    $_SESSION['backurl'] = isset($_GET['backurl']) ? $_GET['backurl'] : '';
    session_write_close();

    $args = new targs();
    $args->name = $name;
    $args->email = $email;
    $args->confirm = $confirm;
    $args->password = $password;
    $args->confirmurl = litepubl::$site->url . '/admin/reguser/' . litepubl::$site->q . 'email=' . urlencode($email);

    tlocal::usefile('mail');
    $lang = tlocal::i('mailusers');
    $theme = ttheme::i();

    $subject = $theme->parsearg($lang->subject, $args);
    $body = $theme->parsearg($lang->body, $args);

    tmailer::sendmail(litepubl::$site->name, litepubl::$options->fromemail, $name, $email, $subject, $body);

    return true;
  }

} //class