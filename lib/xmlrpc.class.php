<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCParser extends IXR_Server  {
  public $XMLResult;
  private $owner;
  
  public function __construct($owner) {
    $this->owner = $owner;
  }
  
  function call($methodname, $args) {
    return $this->owner->call($methodname, $args);
  }
  
  function output($xml) {
    $head = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    $length = strlen($head) + strlen($xml);
    $this->XMLResult = "<?php
    @header('Connection: close');
    @header('Content-Length: $length');
    @header('Content-Type: text/xml; charset=utf-8');
    @header('Date: ".date('r') . "');
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');
    @header('X-Pingback: ". litepublisher::$site->url . "/rpc.xml');
    echo'$head';
    ?>" . $xml;
  }
  
}//class

class TXMLRPC extends titems {
  public $Server;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'xmlrpc';
    $this->dbversion = false;
    $this->cache = false;
    $this->addevents('beforecall', 'aftercall', 'getmethods');
  }
  
  public function request($param) {
    global$HTTP_RAW_POST_DATA;
    if ( !isset( $HTTP_RAW_POST_DATA ) ) {
      $HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
    }
    if ( isset($HTTP_RAW_POST_DATA) ) {
      $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);
    }
    
    if (litepublisher::$debug) {
      tfiler::log("request:\n" . $HTTP_RAW_POST_DATA, 'xmlrpc.txt');
      $reqname = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR  . 'request.xml';
      file_put_contents($reqname, $HTTP_RAW_POST_DATA);
      @chmod($reqname, 0666);
      //$HTTP_RAW_POST_DATA = file_get_contents($GLOBALS['paths']['home'] . 'raw.txt');
    }
    
    $this->getmethods();
    $this->Server = new TXMLRPCParser ($this);
    $this->Server->IXR_Server  ($this->items);
    $Result = $this->Server->XMLResult;
    $this->aftercall();
    if (litepublisher::$debug) tfiler::log("responnse:\n".$Result, 'xmlrpc.txt');
    return $Result;
  }
  
  public function call($method, $args) {
    $this->callevent('beforecall', array($method, &$args));
    if (!isset($this->items[$method])) {
      return new IXR_Error(-32601, "server error. requested method $method does not exist.");
    }
    
    $class = $this->items[$method]['class'];
    $func = $this->items[$method]['func'];
    
    if (empty($class)) {
      if (function_exists($func)) {
        try {
          return call_user_func_array($func, $args);
        } catch (Exception $e) {
          return new IXR_Error($e->getCode(), $e->getMessage());
        }
      } else {
        $this->delete($method);
        return new IXR_Error(-32601, "server error. requested function \"$Func\" does not exist.");
      }
    } else {
      //create class instance
      if (!class_exists($class)) {
        $this->delete($method);
        return new IXR_Error(-32601, "server error. requested class \"$class\" does not exist.");
      }
      $obj = getinstance($class);
      /*
      if (!method_exists($Obj, $Func)) {
        $this->delete($method);
        return new IXR_Error(-32601, "server error. requested object method \"$Function\" does not exist.");
      }
      */
      //return $obj->$func($args);
      try {
        return call_user_func_array(array($obj, $func), $args);
      } catch (Exception $e) {
        //litepublisher::$options->handexception($e);
        //echo (litepublisher::$options->errorlog);
        return new IXR_Error($e->getCode(), $e->getMessage());
      }
    }
  }
  
  public function  add($method, $Function, $ClassName) {
    $this->items[$method] = array(
    'class' => $ClassName,
    'func' => $Function
    );
    $this->save();
  }
  
  public function deleteclass($class) {
    foreach ($this->items as $method => $Item) {
      if ($class == $Item['class']) {
        unset($this->items[$method]);
      }
    }
    $this->save();
  }
  
}//class

class TXMLRPCAbstract extends tevents {
  
  public function uninstall() {
    $caller = TXMLRPC::i();
    $caller->deleteclass(get_class($this));
  }
  
  public static function auth($email, $password, $group) {
    if (litepublisher::$options->auth($email, $password))  {
      if (litepublisher::$options->hasgroup($group)) return true;
    }
    throw new Exception('Bad login/pass combination.', 403);
  }
  
  public static function canedit($email, $password, $idpost) {
    if (litepublisher::$options->auth($email, $password))  {
      if (litepublisher::$options->hasgroup('editor')) return true;
      if (litepublisher::$options->hasgroup('author')) {
        if ($idpost == 0) return true;
        $post = tpost::i($idpost);
        return $post->author == litepublisher::$options->user;
      }
    }
    throw new Exception('Bad login/pass combination.', 403);
  }
  
  public static function xerror($code, $msg) {
    return new IXR_Error($code, $msg);
  }
  
}//class