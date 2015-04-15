<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfacebookregservice extends tregservice {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['name'] = 'facebook';
    $this->data['title'] = 'FaceBook';
    $this->data['icon'] = 'facebook.png';
    $this->data['url'] = '/facebook-oauth2callback.php';
  }
  
  public function getauthurl() {
    $url = 'https://www.facebook.com/dialog/oauth?scope=email&';
    $url .= parent::getauthurl();
    return $url;
  }
  
  //handle callback
  public function request($arg) {
    if ($err = parent::request($arg)) return $err;
    $code = $_REQUEST['code'];
    $resp = http::get('https://graph.facebook.com/oauth/access_token?' . http_build_query(array(
    'code' => $code,
    'client_id' => $this->client_id,
    'client_secret' => $this->client_secret,
    'redirect_uri' => litepublisher::$site->url . $this->url,
    //'grant_type' => 'authorization_code'
    )));
    
    if ($resp) {
      $params = null;
      parse_str($resp, $params);
      
      if ($r = http::get('https://graph.facebook.com/me?access_token=' . $params['access_token'])) {
        $info = json_decode($r);
        return $this->adduser(array(
        'service' => $this->name,
        'uid' => isset($info->id) ? $info->id : '',
        'email' => isset($info->email) ? $info->email : '',
        'name' => $info->name,
        'website' => isset($info->link) ? $info->link : ''
        ), $info);
      }
    }
    
    return $this->errorauth();
  }
  
  protected function getadmininfo($lang) {
    return array(
    'regurl' => 'https://developers.facebook.com/apps',
    'client_id' => 'App ID',
    'client_secret' =>'App Secret'
    );
  }
  
}//class