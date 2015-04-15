<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsubscribers extends tadminform {
  private $iduser;
  private $newreg;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->section = 'subscribers';
    $this->iduser = false;
    $this->newreg = false;
  }
  
  public function request($arg) {
    $this->cache = false;
    if (!($this->iduser = litepublisher::$options->user)) {
      //trick - hidden registration of comuser. Auth by get
      $users = tusers::i();
      if (isset($_GET['auth']) && ($cookie = trim($_GET['auth']))) {
        if (($this->iduser = $users->findcookie($cookie)) && litepublisher::$options->reguser) {
          if ('comuser' == $users->getvalue($this->iduser, 'status')) {
            // bingo!
            $this->newreg = true;
            $item = $users->getitem($this->iduser);
            $item['status'] = 'approved';
            $item['password'] = '';
            $item['idgroups'] =  'commentator';
            
            $cookie = md5uniq();
            $expired = time() + 31536000;
            
            $item['cookie'] = litepublisher::$options->hash($cookie);
            $item['expired'] = sqldate($expired);
            $users->edit($this->iduser, $item);
            
            litepublisher::$options->user = $this->iduser;
            litepublisher::$options->updategroup();
            
            litepublisher::$options->setcookie('litepubl_user_id', $this->iduser, $expired);
            litepublisher::$options->setcookie('litepubl_user', $cookie, $expired);
          } else {
            $this->iduser = false;
          }
        }
      }
    }
    
    if (!$this->iduser) {
      $url = litepublisher::$site->url . '/admin/login/' . litepublisher::$site->q . 'backurl=' . rawurlencode('/admin/subscribers/');
      return litepublisher::$urlmap->redir($url);
    }
    
    if ('hold' == tusers::i()->getvalue($this->iduser, 'status')) return 403;
    return parent::request($arg);
  }
  
  public function gethead() {
    $result = parent::gethead();
    $result .= tadminmenus::i()->heads;
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $html= $this->html;
    $lang = tlocal::admin();
    $args = new targs();
    if ($this->newreg) $result .=$html->h4->newreg;
    
    $subscribers=  tsubscribers::i();
    $items = $subscribers->getposts($this->iduser);
    if (count($items) == 0) return $html->h4->nosubscribtions;
    tposts::i()->loaditems($items);
    $args->default_subscribe = tuseroptions::i()->getvalue($this->iduser, 'subscribe') == 'enabled';
    $args->formtitle = tusers::i()->getvalue($this->iduser, 'email') . ' ' . $lang->formhead;
    $result .= $html->adminform('[checkbox=default_subscribe]' .
    $table = $html->tableposts($items, array(
    array('left', $lang->post, '<a href="$site.url$post.url" title="$post.title">$post.title</a>')
    )), $args);
    
    return $html->fixquote($result);
  }
  
  public function processform() {
    tuseroptions::i()->setvalue($this->iduser, 'subscribe', isset($_POST['default_subscribe']) ? 'enabled' : 'disabled');
    
    $subscribers = tsubscribers::i();
    foreach ($_POST as $name => $value) {
      if (strbegin($name, 'checkbox-')) {
        $subscribers->remove((int) $value, $this->iduser);
      }
    }
    
    return $this->html->h4->unsubscribed;
  }
  
}//class