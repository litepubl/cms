<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlivejournalposter extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['host'] = '';
    $this->data['login'] = '';
    $this->data['password'] = '';
    $this->data['community'] = '';
    $this->data['privacy'] = 'public';
    $this->data['template'] = '';
  }
  
  public function sendpost($id) {
    if ($this->host == '' || $this->login == '') return false;
    $post = tpost::i($id);
    ttheme::$vars['post'] = $post;
    $theme = ttheme::i();
    $content = $theme->parse($this->template);
    $date = getdate($post->posted);
    
    if ($post->status != 'published') return;
    $meta = $post->meta;
    
    $client = new IXR_Client($this->host, '/interface/xmlrpc');
    //$client = new IXR_Client($this->host, '/rpc.xml');
    if (!$client->query('LJ.XMLRPC.getchallenge'))  {
      if (litepublisher::$debug) tfiler::log('live journal: error challenge');
      return false;
    }
    $response = $client->getResponse();
    $challenge = $response['challenge'];
    
    $args = array(
    'username' => $this->login,
    'auth_method' => 'challenge',
    'auth_challenge' => $challenge,
    'auth_response' => md5($challenge . md5($this->password)),
    'ver' => "1",
    'event' => $content,
    'subject' => $post->title,
    'year' => $date['year'],
    'mon' => $date['mon'],
    'day' => $date['mday'],
    'hour' => $date['hours'],
    'min' => $date['minutes'],
    'props' => array(
    'opt_nocomments' => !$post->commentsenabled,
    'opt_preformatted' => true,
    'taglist' => $post->tagnames
    )
    );
    
    switch($this->privacy) {
      case "public":
      $args['security'] = "public";
      break;
      case "private":
      $args['security'] = "private";
      break;
      case "friends":
      $args['security'] = "usemask";
      $args['allowmask'] = 1;
    }
    
    if($this->community != '') $args['usejournal'] = $this->community;
    
    if (isset($meta->ljid) ) {
      $method = 'LJ.XMLRPC.editevent';
      $args['itemid'] = $meta->ljid;
    } else {
      $method = 'LJ.XMLRPC.postevent';
    }
    
    if (!$client->query($method, $args)) {
      if (litepublisher::$debug) tfiler::log('Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage());
      return  false;
    }
    
    if (!isset($meta->ljid)) {
      $response = $client->getResponse();
      $meta->ljid = $response['itemid'];
    }
    return $meta->ljid;
  }
  
}//class