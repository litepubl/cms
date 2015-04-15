<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class toauth extends tdata {
  public $urllist;
  public $key;
  public $secret;
  public $token;
  public $tokensecret;
  public $timeout;
  public $response;
  public $response_headers;
  
  protected function create() {
    parent::create();
    $this->basename = 'oauth';
    $this->key = '';
    $this->secret = '';
    $this->token = '';
    $this->tokensecret = '';
    $this->timeout = 2;
    $this->urllist= array(
    'request' => 'https://api.twitter.com/oauth/request_token',
    'authorize' => 'https://api.twitter.com/oauth/authorize',
    'access' => 'https://api.twitter.com/oauth/access_token',
    //'callback' => litepublisher::$site->url . '/twitter-oauth1callback.php'
    'callback' => ''
    );
  }
  
  //to override in child classes
  public function settokens($token, $secret) {
    $this->token = $token;
    $this->tokensecret  = $secret;
    return $token && $secret;
  }
  
  public function getkeys() {
    return array();
  }
  
  public function getextraheaders() {
    return array();
  }
  
  private function getsign($keys, $url, $method='GET'){
    $parsed = parse_url($url);
    if (isset($parsed['query'])){
      parse_str($parsed['query'], $query);
      $keys = array_merge($keys, $query);
    }
    
    if (!isset($keys['oauth_key'])) $keys['oauth_key'] = $this->key;
    $keys['oauth_version']		= '1.0';
    $keys['oauth_nonce']		= md5('_oauth_rand_' . microtime() . mt_rand());
    $keys['oauth_timestamp']	= time();
    $keys['oauth_consumer_key']	= $keys['oauth_key'];
    $keys['oauth_signature_method']	= 'HMAC-SHA1';
    $keys['oauth_signature']	= $this->getsignature($keys, $url, $method);
    return $keys;
  }
  
  public function get_url(array $keys, $url, $method='GET'){
    return $this->normalize_url($url) . '?' . $this->getparams($this->getsign($keys, $url, $method));
  }
  
  public function getdata(array $keys, $url, $params=array(), $method='GET'){
    $url = $this->get_url($keys, $url, $params, $method);
    if ($method == 'POST'){
      list($url, $postdata) = explode('?', $url, 2);
    }else{
      $postdata = null;
    }
    
    return $this->dorequest($url, $method, $postdata);
  }
  
  private function getsignature($keys, $url, $method){
    $sig = array(
    rawurlencode(strtoupper ($method)),
    preg_replace('/%7E/', '~', rawurlencode($this->normalize_url($url))),
    rawurlencode($this->get_signable($keys))
    );
    
    $key = rawurlencode($this->secret) . '&';
    if ($this->tokensecret != '') {
      $key .= rawurlencode($this->tokensecret);
    }
    
    $raw = implode('&', $sig);
    return base64_encode($this->hmac_sha1($raw, $key, TRUE));
  }
  
  private function normalize_url($url){
    $parts = parse_url($url);
    $port = '';
    if (array_key_exists('port', $parts) && $parts['port'] != '80'){
      $port = ':' . $parts['port'];
    }
    return $parts['scheme'] . '://' .  $parts['host'] . $port . $parts['path'];
  }
  
  private function get_signable($params){
    if (isset($params['oauth_signature'])) unset($params['oauth_signature']);
    ksort($params);
    $total = array();
    foreach ($params as $k => $v) {
      $total[] = rawurlencode($k) . '=' . rawurlencode($v);
    }
    return implode('&', $total);
  }
  
  private function getparams($params){
    $result = array();
    foreach ($params as $k => $v) {
      $result[] = rawurlencode($k) . '=' . rawurlencode($v);
    }
    return implode('&', $result);
  }
  
  public function getauthorization($keys, $url) {
    $params = $this->getsign($keys, $url, 'post');
    ksort($params);
    $result = array();
    foreach ($params as $k => $v) {
      $result[] = sprintf('%s="%s"', $k, rawurlencode($v));
    }
    return implode(', ', $result);
  }
  
  private function hmac_sha1($data, $key, $raw=TRUE){
    if (strlen($key) > 64){
      $key =  pack('H40', sha1($key));
    }
    
    if (strlen($key) < 64){
      $key = str_pad($key, 64, chr(0));
    }
    
    $_ipad = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));
    $_opad = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));
    
    $hex = sha1($_opad . pack('H40', sha1($_ipad . $data)));
    if (!$raw) return $hex;
    $bin = '';
    while (strlen($hex)){
      $bin .= chr(hexdec(substr($hex, 0, 2)));
      $hex = substr($hex, 2);
    }
    return $bin;
  }
  
  public function get_token(array $keys){
    if ($bits = $this->getbits($this->get_url($keys, $this->urllist['request']))) {
      if ($this->settokens($bits['oauth_token'], $bits['oauth_token_secret'])) return $bits;
    }
    return false;
  }
  
  private function getbits($url){
    if ($crap = $this->dorequest($url)) {
      $bits = explode('&', $crap);
      $result = array();
      foreach ($bits as $bit){
        list($k, $v) = explode('=', $bit, 2);
        $result[urldecode($k)] = urldecode($v);
      }
      
      return $result;
    }
    return false;
  }
  
  public function getaccess($keys) {
    if ($bits = $this->getbits($this->get_url($keys, $this->urllist['access']))) {
      if ($this->settokens($bits['oauth_token'], $bits['oauth_token_secret'])) return $bits;
    }
    return false;
  }
  
  private function dorequest($url, $method='GET', $postdata=null){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); 	// Get around error 417
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
    
    if ($method == 'POST'){
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    }
    
    $response = curl_exec($ch);
    $headers = curl_getinfo($ch);
    curl_close($ch);
    if ($headers['http_code'] != '200') return false;
    return $response;
  }
  
  public function getrequesttoken() {
    $keys = $this->getkeys();
    if ($tokens = $this->get_token($keys)) {
      return $tokens;
      /*
      $keys['oauth_token'] = $tokens['oauth_token'];
      if ($this->getaccess($keys)) return true;
      */
    }
    return false;
  }
  
  public function get_authorize_url() {
    return $this->urllist['authorize'] . sprintf('?oauth_token=%s&&oauth_callback=%s',
    rawurlencode($this->token), rawurlencode($this->urllist['callback']));
  }
  
  public function getaccesstoken($oauth_verifier) {
    $keys = $this->getkeys();
    $keys['oauth_token'] = $this->token;
    $keys['oauth_verifier'] = $oauth_verifier;
    if ($result = $this->getaccess($keys)) {
      return $result;
    }
    return false;
  }
  
  public function postdata($url, array $post) {
    $a = array();
    foreach ($post as $k => $v) {
      $a[] = sprintf('%s=%s', rawurlencode($k), rawurlencode($v));
    }
    $postdata = implode('&', $a);
    
    $keys = array('oauth_token' => $this->token);
    
    $authorization = $this->getauthorization($keys, $url . '?' . $postdata);
    $headers = array(
    'Authorization: OAuth '. $authorization,
    'Content-Length: ' . strlen($postdata )
    );
    
    $headers = array_merge($headers, $this->getextraheaders());
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata );
    
    $this->response = curl_exec($ch);
    $this->response_headers = curl_getinfo($ch);
    curl_close($ch);
    if ($this->response_headers['http_code'] != '200') return false;
    return $this->response;
  }
  
  public function get_data($url) {
    $keys = $this->getkeys();
    $keys['oauth_token'] = $this->token;
    return http::get($this->get_url($keys, $url));
  }
  
}//class