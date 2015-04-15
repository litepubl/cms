<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tjsonserver extends tevents {
  public $debug;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'jsonserver';
    $this->cache = false;
    $this->addevents('beforerequest', 'beforecall', 'aftercall');
    $this->data['eventnames'] = &$this->eventnames;
    $this->map['eventnames'] = 'eventnames';
    $this->data['url'] = '/admin/jsonserver.php';
    $this->debug = false;
  }
  
  public function getpostbody() {
    global$HTTP_RAW_POST_DATA;
    if ( !isset( $HTTP_RAW_POST_DATA ) ) {
      $HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
    }
    if ( isset($HTTP_RAW_POST_DATA) ) {
      $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);
    }
    
    if (litepublisher::$debug) {
      tfiler::log("request:\n" . $HTTP_RAW_POST_DATA, 'json.txt');
      /*
      $reqname = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR  . 'request.json';
      file_put_contents($reqname, $HTTP_RAW_POST_DATA);
      @chmod($reqname, 0666);
      $HTTP_RAW_POST_DATA = file_get_contents($GLOBALS['paths']['home'] . 'raw.txt');
      */
    }
    
    return $HTTP_RAW_POST_DATA;
  }
  
  public function get_json_args() {
    if ($s = trim($this->getpostbody())) {
      return json_decode($s, true);
    }
    return false;
  }
  
  public function getargs() {
    if (isset($_GET['method'])) return $_GET;
    
    if (isset($_POST['method'])) {
      tguard::post();
      return $_POST;
    }
    
    if (isset($_POST['json'])) {
      tguard::post();
      if (($s = trim($_POST['json'])) && ($args = json_decode($s, true))) {
        if (isset($args['method'])) return $args;
      }
    }
    
    if ($args = $this->get_json_args()) {
      if (isset($args['method'])) return $args;
    }
    
    return false;
  }
  
  public function request($idurl) {
    $this->beforerequest();
    $args = $this->getargs();
    if (!$args || !isset($args['method'])) return $this->json_error(false, 403, 'Method not found in arguments');
    
    $rpc = isset($args['jsonrpc']) ? ($args['jsonrpc'] == '2.0') : false;
    $id =$rpc && isset($args['id']) ? $args['id'] : false;
    
    if (!isset($this->events[$args['method']])) {
      return $this->json_error($id, 404, sprintf('Method "%s" not found', $args['method']));
    }
    
    if ($rpc) {
      $params = isset($args['params']) ? $args['params'] : array();
    } else {
      $params = $args;
    }
    
    if (isset($params['litepubl_user'])) $_COOKIE['litepubl_user'] = $params['litepubl_user'];
    if (isset($params['litepubl_user_id'])) $_COOKIE['litepubl_user_id'] = $params['litepubl_user_id'];
    
    $a = array(&$params);
    $this->callevent('beforecall', $a);
    try {
      $result = $this->callevent($args['method'], $a);
    } catch (Exception $e) {
      if (litepublisher::$debug || $this->debug) {
        litepublisher::$options->handexception($e);
        throw new Exception(litepublisher::$options->errorlog);
      }
      
      return $this->json_error($id, $e->getCode(), $e->getMessage());
    }
    
    $this->callevent('aftercall', array(&$result, $args));
    
    $resp= array('jsonrpc' => '2.0');
    if (is_array($result) && isset($result['error'])) {
      $resp['error'] = $result['error'];
    } else {
      $resp['result'] = $result;
      if (isset($params['slave']) && is_array($params['slave'])) {
        try {
          $slave_result= $this->callevent($params['slave']['method'], array($params['slave']['params']));
        } catch (Exception $e) {
          $slave_result = array('error' => array(
          'message' => $e->getMessage(),
          'code' => $e->getCode()
          ));
        }
        
        $resp['result']['slave'] = $slave_result;
      }
    }
    
    if ($id) $resp['id'] = $id;
    return $this->json($resp);
  }
  
  public function json($result) {
    $js = tojson($result);
    //if (litepublisher::$debug) tfiler::log("response:\n".$js, 'json.txt');
    
    return "<?php
    header('Connection: close');
    header('Content-Length: ". strlen($js) . "');
    header('Content-Type: text/javascript; charset=utf-8');
    header('Date: ".date('r') . "');
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');
    ?>" .
    $js;
    
    //header('Content-Type: application/json');
  }
  
  public function json_error($id, $code, $message) {
    $result = array(
    'jsonrpc' => '2.0',
    'error' => array(
    'code' =>       $code,
    'message' => $message,
    ));
    
    if ($id) $result['id'] = $id;
    return $this->json($result);
  }
  
  public function addevent($name, $class, $func) {
    if (!in_array($name, $this->eventnames)) $this->eventnames[] = $name;
    return parent::addevent($name, $class, $func);
  }
  
  public function delete_event($name) {
    if (isset($this->events[$name])) {
      unset($this->events[$name]);
      array_delete_value($this->eventnames, $name);
      $this->save();
    }
  }
  
}//class