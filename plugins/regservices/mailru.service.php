<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmailruregservice extends tregservice {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['name'] = 'mailru';
    $this->data['title'] = 'mail.ru';
    $this->data['icon'] = 'mailru.png';
    $this->data['url'] = '/mailru-oauth2callback.php';
  }
  
  public function getauthurl() {
    $url = 'https://connect.mail.ru/oauth/authorize?';
    $url .= parent::getauthurl();
    return $url;
  }
  
  //handle callback
  public function sign(array $request_params, $secret_key) {
    ksort($request_params);
    $params = '';
    foreach ($request_params as $key => $value) {
      $params .= "$key=$value";
    }
    return md5($params . $secret_key);
  }
  
  public function request($arg) {
    if ($err = parent::request($arg)) return $err;
    $code = $_REQUEST['code'];
    $resp = http::post('https://connect.mail.ru/oauth/token', array(
    'code' => $code,
    'client_id' => $this->client_id,
    'client_secret' => $this->client_secret,
    'redirect_uri' => litepublisher::$site->url . $this->url,
    'grant_type' => 'authorization_code'
    ));
    
    if ($resp) {
      $tokens  = json_decode($resp);
      
      $params = array(
      'method' => 'users.getInfo',
      'app_id' => $this->client_id,
      'session_key' => $tokens->access_token,
      'uids' => $tokens->x_mailru_vid,
      'secure' => '1',
      'format' => 'json',
      );
      
      ksort($params);
      $params['sig'] = $this->sign($params, $this->client_secret);
      if ($r = http::get('http://www.appsmail.ru/platform/api?' . http_build_query($params))) {
        $js = json_decode($r);
        $info = $js[0];
        return $this->adduser(array(
        'uid' => $info->uid,
        'email' => isset($info->email) ? $info->email : '',
        'name' => $info->nick,
        'website' => isset($info->link) ? $info->link : ''
        ), $info);
      }
    }
    
    return $this->errorauth();
  }
  
  protected function getadmininfo($lang) {
    return array(
    'regurl' => 'http://api.mail.ru/sites/my/add',
    'client_id' => 'ID',
    'client_secret' =>$lang->mailru_secret
    );
  }
  
}//class