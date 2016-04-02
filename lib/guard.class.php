<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tguard {
  //prevent double call post()
  private static $posted;

  public static function post() {
    if (is_bool(static::$posted)) return static::$posted;
    static::$posted = false;
    if (!isset($_POST) || !count($_POST)) return false;
    if (get_magic_quotes_gpc()) {
      foreach ($_POST as $name => $value) {
        $_POST[$name] = stripslashes($_POST[$name]);
      }
    }
    static::$posted = true;
    return true;
  }

  public static function is_xxx() {
    if (isset($_GET['ref'])) {
      $ref = $_GET['ref'];
      $url = $_SERVER['REQUEST_URI'];
      $url = substr($url, 0, strpos($url, '&ref='));
      if ($ref == md5(litepubl::$secret . litepubl::$site->url . $url . litepubl::$options->solt)) return false;
    }

    $host = '';
    if (!empty($_SERVER['HTTP_REFERER'])) {
      $p = parse_url($_SERVER['HTTP_REFERER']);
      $host = $p['host'];
    }
    return $host != $_SERVER['HTTP_HOST'];
  }

  public static function checkattack() {
    if (litepubl::$options->xxxcheck && static::is_xxx()) {
      turlmap::nocache();
      tlocal::usefile('admin');
      if ($_POST) {
        die(tlocal::get('login', 'xxxattack'));
      }
      if ($_GET) {
        die(tlocal::get('login', 'confirmxxxattack') . sprintf(' <a href="%1$s">%1$s</a>', $_SERVER['REQUEST_URI']));
      }
    }
    return false;
  }

} //class