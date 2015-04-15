<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttwitterregservice extends tregservice {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['name'] = 'twitter';
    $this->data['title'] = 'Twitter';
    $this->data['icon'] = 'twitter.png';
    $this->data['url'] = '/twitter-oauth1callback.php';
  }
  
  public function getauthurl() {
    $oauth = $this->getoauth();
    if ($tokens = $oauth->getrequesttoken()) {
      tsession::start(md5($tokens['oauth_token']));
      $_SESSION['tokens'] = $tokens;
      session_write_close();
      return $oauth->get_authorize_url();
    }
    return false;
  }
  
  public function getoauth() {
    $oauth = new toauth();
    $oauth->urllist['callback'] = litepublisher::$site->url . $this->url;
    $oauth->key = $this->client_id;
    $oauth->secret = $this->client_secret;
    return $oauth;
  }
  
  //handle callback
  public function request($arg) {
    $this->cache = false;
    turlmap::nocache();
    
    if (empty($_GET['oauth_token'])) return 403;
    tsession::start(md5($_GET['oauth_token']));
    if (!isset($_SESSION['tokens'])) {
      session_destroy();
      return 403;
    }
    
    $tokens = $_SESSION['tokens'];
    session_destroy();
    $oauth = $this->getoauth();
    $oauth->settokens($tokens['oauth_token'], $tokens['oauth_token_secret']);
    
    if ($tokens  = $oauth->getaccesstoken($_REQUEST['oauth_verifier'])) {
      if ($r = $oauth->get_data('https://api.twitter.com/1/account/verify_credentials.json')) {
        $info = json_decode($r);
        return $this->adduser(array(
        'uid' => $info->id,
        'name' => $info->name,
        'website' => 'http://twitter.com/account/redirect_by_id?id='.$info->id_str
        ), $info);
      }
    }
    
    return $this->errorauth();
  }
  
  protected function getadmininfo($lang) {
    return array(
    'regurl' => 'https://dev.twitter.com/apps/new',
    'client_id' => 'Consumer key',
    'client_secret' =>'Consumer secret'
    );
  }
  
}//class