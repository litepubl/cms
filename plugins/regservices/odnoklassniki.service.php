<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class todnoklassnikiservice extends tregservice {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['public_key'] = '';
    $this->data['name'] = 'odnoklassniki';
    $this->data['title'] = 'odnoklassniki.ru';
    $this->data['icon'] = 'odnoklassniki.png';
    $this->data['url'] = '/odnoklassniki-oauth2callback.php';
  }
  
  public function getauthurl() {
    $url = 'http://www.odnoklassniki.ru/oauth/authorize?';
    $url .= 'response_type=code';
    $url .= '&redirect_uri=' . urlencode(litepublisher::$site->url .
    $this->url . litepublisher::$site->q . 'state=' . $this->newstate());
    $url .= '&client_id=' . $this->client_id;
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
    $resp = http::post('http://api.odnoklassniki.ru/oauth/token.do', array(
    'grant_type' => 'authorization_code',
    'code' => $code,
    'client_id' => $this->client_id,
    'client_secret' => $this->client_secret,
    'redirect_uri' => litepublisher::$site->url . $this->url . litepublisher::$site->q . 'state=' . $_GET['state'],
    ));
    
    if ($resp) {
      $tokens  = json_decode($resp);
      if (isset($tokens ->error)) return 403;
      
      $params = array(
      'application_key' => $this->public_key,
      'client_id' => $this->client_id,
      'method' => 'users.getCurrentUser',
      'format' => 'JSON',
      );
      
      $params['sig'] = strtolower($this->sign($params, md5($tokens->access_token . $this->client_secret)));
      $params['access_token'] = $tokens->access_token;
      
      if ($r = http::post('http://api.odnoklassniki.ru/fb.do', $params)) {
        $js = json_decode($r);
        if (!isset($js->error)) {
          return $this->adduser(array(
          'uid' => $js->uid,
          'name' => $js->name,
          'website' => isset($js->link) ? $js->link : ''
          ), $js);
        }
      }
    }
    
    return $this->errorauth();
  }
  
  protected function getadmininfo($lang) {
    return array(
    'regurl' => 'http://api.mail.ru/sites/my/add',
    'client_id' => $lang->odnoklass_id,
    'client_secret' =>$lang->odnoklass_secret,
    'public_key' => $lang->odnoklass_public_key
    );
  }
  
  public function gettab($html, $args, $lang) {
    $a = $this->getadmininfo($lang);
    $result = $html->p(sprintf($lang->odnoklass_reg, 'http://dev.odnoklassniki.ru/wiki/display/ok/How+to+add+application+on+site'));
    
    $result .= $html->getinput('text', "client_id_$this->name", tadminhtml::specchars($this->client_id), $a['client_id']) ;
    $result .= $html->getinput('text', "client_secret_$this->name", tadminhtml::specchars($this->client_secret), $a['client_secret']) ;
    
    $result .= $html->getinput('text', "public_key_$this->name", tadminhtml::specchars($this->public_key), $lang->odnoklass_public_key);
    return $result;
  }
  
  public function processform() {
    if (isset($_POST["public_key_$this->name"])) $this->public_key = $_POST["public_key_$this->name"];
    parent::processform();
  }
  
}//class