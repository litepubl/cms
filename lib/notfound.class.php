<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tforbidden extends tevents_itemplate implements itemplate {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'forbidden';
    $this->data['text'] = '';
  }
  
public function request($arg) {}
public function gettitle() {}
  public function  httpheader() {
    return '<?php Header(\'HTTP/1.0 403 Forbidden\', true, 403); ?>' . turlmap::htmlheader(false);
  }
  
  public function getcont() {
    $this->cache = false;
    $view = tview::getview($this);
    $theme = $view->theme;
    if ($this->text != '') return $theme->simple($this->text);
    
    $lang = tlocal::i('default');
    if ($this->basename == 'forbidden') {
      return $theme->simple(sprintf('<h1>%s</h1>', $lang->forbidden));
    } else {
      return $theme->parse($theme->templates['content.notfound']);
    }
  }
  
}//class

class tnotfound404 extends tforbidden {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'notfound';
    $this->data['notify'] = false;
  }
  
  public function  httpheader() {
    return "<?php Header( 'HTTP/1.0 404 Not Found'); ?>" . turlmap::htmlheader(false);
  }
  
  function getcont() {
    if ($this->notify) $this->sendmail();
    return parent::getcont();
  }
  
  private function sendmail() {
    $args = new targs();
    $args->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $args->ref =  isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    
    tlocal::usefile('mail');
    $lang = tlocal::i('notfound');
    $theme = ttheme::i();
    
    $subject = $theme->parsearg($lang->subject, $args);
    $body = $theme->parsearg($lang->body, $args);
    
    tmailer::sendtoadmin($subject, $body, true);
  }
  
}//class