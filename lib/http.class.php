<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class http {
  public static $timeout = 10;
  
  public static function get($url, $headers = false) {
    $parsed = @parse_url($url);
    if ( !$parsed || !is_array($parsed) ) return false;
    if ( !isset($parsed['scheme']) || !in_array($parsed['scheme'], array('http','https')) ) {
      $url = 'http://' . $url;
      $parsed['scheme'] = 'http';
    }
    
    if (($parsed['scheme'] == 'http') && ini_get('allow_url_fopen') && !(is_array($headers) && count($headers))) {
      if($fp = @fopen( $url, 'r' )) {
        @stream_set_timeout($fp, self::$timeout);
        
        $result = '';
        while( $remote_read = fread($fp, 4096) )  $result .= $remote_read;
        fclose($fp);
        return $result;
      }
      return false;
    } elseif ( function_exists('curl_init') ) {
      $ch = curl_init();
      curl_setopt ($ch, CURLOPT_URL, $url);
      curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, self::$timeout);
      curl_setopt ($ch, CURLOPT_TIMEOUT, self::$timeout);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
      if (is_array($headers) && count($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      
      /*
      curl_setopt($ch, CURLOPT_VERBOSE , true);
      curl_setopt($ch, CURLOPT_STDERR, fopen(litepublisher::$paths->data . 'logs/curl.txt', 'w+'));
      */
      
      if (!ini_get('open_basedir')  && !ini_get('safe_mode') ) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $result= curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (($code == 200) || ($code == 201)) return $result;
        return false;
      } else {
        return self::curl_follow($ch);
      }
    }
    
    return false;
  }
  
  public static function createcurl($url, $post, $headers = false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, self::$timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeout);
    if (is_array($headers) && count($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
    
    curl_setopt($ch, CURLOPT_VERBOSE , true);
    curl_setopt($ch, CURLOPT_STDERR, fopen(litepublisher::$paths->data . 'logs/curl.txt', 'w+'));
    
    return $ch;
  }
  
  public static function post($url, $post, $headers = false) {
    $ch = self::createcurl($url, $post, $headers);
    $response = curl_exec($ch);
    //$respheaders = curl_getinfo($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (in_array($code, array('200', '201'))) return $response;
    return false;
  }
  
  public static function curl_follow($ch, $maxredirect = 10) {
    //manual redirect
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    do {
      $result = curl_exec($ch);
      $headers = curl_getinfo($ch);
      //$code = $headers['http_code'];
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($code == 404) return false;
      if ($code == 301 || $code == 302 || $code == 307) {
        if (isset($headers['redirect_url'])) {
          curl_setopt($ch, CURLOPT_URL, $headers['redirect_url']);
        } else {
          if(preg_match('/^Location:\s+(.*)$/mi', $result, $m)) {
            curl_setopt($ch, CURLOPT_URL, trim($m[1]));
          } else {
            //redirect without url
            return false;
          }
        }
      } else {
        break;
      }
    } while ($maxredirect --);
    
    curl_close($ch);
    return substr($result, strpos($result, "\r\n\r\n") + 4);
  }
  
}//class