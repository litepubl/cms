<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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
    return litepublisher::$options->authcookie();
  }
  
  public function request($arg) {
    if (!litepublisher::$options->usersenabled || !litepublisher::$options->reguser) return 403;
    parent::request($arg);
    
    if (!empty($_GET['confirm'])) {
      $confirm = $_GET['confirm'];
      $email = $_GET['email'];
      tsession::start('reguser-' . md5(litepublisher::$options->hash($email)));
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
        litepublisher::$options->user = $id;
        litepublisher::$options->updategroup();
        litepublisher::$options->setcookies($cookie, $expired);
      } else {
        $this->regstatus = 'error';
      }
    }
  }
  
  public function getcontent() {
    $result = '';
    $html = $this->html;
    $lang = tlocal::admin('users');
    if ($this->logged) return $html->h4red($lang->logged . ' ' . $html->getlink('/admin/', $lang->adminpanel));
    
    $args = new targs();
    
    if ($this->regstatus) {
      switch ($this->regstatus) {
        case 'ok':
        $backurl = $this->backurl;
        if (!$backurl) $backurl =  tusergroups::i()->gethome(litepublisher::$options->group);
        if (!strbegin($backurl, 'http://')) $backurl = litepublisher::$site->url . $backurl;
        return $html->h4($lang->successreg . ' ' . $html->getlink($backurl, $lang->continue));
        
        case 'mail':
        return $html->h4->waitconfirm;
        
        case 'error':
        $result .= $html->h4->invalidregdata;
      }
    }
    
    $form = '';
    foreach (array('email', 'name') as $name) {
      $args->$name = isset($_POST[$name]) ? $_POST[$name] : '';
      $form .= "[text=$name]";
    }
    $lang = tlocal::i('users');
    $args->formtitle = $lang->regform;
    $args->data['$lang.email'] = 'email';
    $result .= $this->widget;
    if (isset($_GET['backurl'])) {
      //normalize
      $result = str_replace('&amp;backurl=', '&backurl=', $result);
      $result = str_replace('backurl=', 'backurl=' . urlencode($_GET['backurl']), $result);
      $result = str_replace('backurl%3D', 'backurl%3D' . urlencode(urlencode($_GET['backurl'])), $result);
    }
    
    $result .= $html->adminform($form, $args);
    $result = str_replace(' action=""',' action="' . litepublisher::$site->url . '/admin/reguser/"', $result);
    $this->callevent('oncontent', array(&$result));
    return $result;
  }
  
  public function processform() {
    $this->regstatus = 'error';
    try {
      if ($this->reguser($_POST['email'], $_POST['name']))    $this->regstatus = 'mail';
    } catch (Exception $e) {
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
    
    tsession::start('reguser-' . md5(litepublisher::$options->hash($email)));
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
    $args->confirmurl = litepublisher::$site->url . '/admin/reguser/' . litepublisher::$site->q . 'email=' . urlencode($email);
    
    tlocal::usefile('mail');
    $lang = tlocal::i('mailusers');
    $theme = ttheme::i();
    
    $subject = $theme->parsearg($lang->subject, $args);
    $body = $theme->parsearg($lang->body, $args);
    
    tmailer::sendmail(litepublisher::$site->name, litepublisher::$options->fromemail,
    $name, $email, $subject, $body);
    
    return true;
  }
  
}//class