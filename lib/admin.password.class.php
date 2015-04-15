<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpassword extends tadminform {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->section = 'password';
  }
  
  public function getcontent() {
    $html = $this->html;
    $args = new targs();
    $lang = tlocal::admin('password');
    if (empty($_GET['confirm'])) {
      $args->formtitle = $lang->enteremail;
      return $html->adminform('[text=email]', $args);
    } else {
      $email = $_GET['email'];
      $confirm = $_GET['confirm'];
      tsession::start('password-restore-' . md5(litepublisher::$options->hash($email)));
      if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($confirm != $_SESSION['confirm'])) {
        if (!isset($_SESSION['email'])) session_destroy();
        return $html->h4->notfound;
      }
      $password = $_SESSION['password'];
      session_destroy();
      if ($id = $this->getiduser($email)) {
        if ($id == 1) {
          litepublisher::$options->changepassword($password);
        } else {
          tusers::i()->changepassword($id, $password);
        }
        $args->password = $password;
        $args->email = $email;
        return $html->newpassword($args);
      } else {
        return $html->h4->notfound;
      }
    }
  }
  
  public function getiduser($email) {
    if (empty($email)) return false;
    if ($email == strtolower(trim(litepublisher::$options->email))) return 1;
    return tusers::i()->emailexists($email);
  }
  
  public function processform() {
    try {
      $this->restore($_POST['email']);
    } catch (Exception $e) {
      return sprintf('<h4 class="red">%s</h4>', $e->getMessage());
    }
    
    return $this->html->h4->success;
  }
  
  public function restore($email) {
    $lang = tlocal::admin('password');
    $email = strtolower(trim($email));
    if (empty($email)) return $this->error($lang->error);
    $id = $this->getiduser($email);
    if (!$id) return $this->error($lang->error);
    
    $args = new targs();
    
    tsession::start('password-restore-' .md5(litepublisher::$options->hash($email)));
    if (!isset($_SESSION['count'])) {
      $_SESSION['count'] =1;
    } else {
      if ($_SESSION['count']++ > 3) return $this->error($lang->outofcount);
    }
    
    $_SESSION['email'] = $email;
    $password = md5uniq();
    $_SESSION['password'] = $password;
    $_SESSION['confirm'] = md5rand();
    $args->confirm = $_SESSION['confirm'];
    session_write_close();
    
    $args->email = urlencode($email);
    if ($id == 1) {
      $name = litepublisher::$site->author;
    } else {
      $item = tusers::i()->getitem($id);
      $args->add($item);
      $name = $item['name'];
    }
    
    $args->password = $password;
    tlocal::usefile('mail');
    $lang = tlocal::i('mailpassword');
    $theme = ttheme::i();
    
    $subject = $theme->parsearg($lang->subject, $args);
    $body = $theme->parsearg($lang->body, $args);
    
    tmailer::sendmail(litepublisher::$site->name, litepublisher::$options->fromemail,
    $name, $email, $subject, $body);
    return true;
  }
  
}//class