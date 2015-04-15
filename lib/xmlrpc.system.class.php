<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCSystem extends TXMLRPCAbstract {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function listMethods() {
    $caller = TXMLRPC::i();
    return array_keys($caller->items);
  }
  
  public function methodSignature($name) {
    $caller = TXMLRPC::i();
    if (!$caller->itemexists($name)) return new IXR_Error(-32601, "server error. requested method $name does not exist.");
    $item = $caller->getitem($name);
    if ($item['class'] != __class__) {
      $obj = getinstance($item['class']);
      if (method_exists($obj, 'getsignature')) {
        return $obj->getsignature($name);
      } else {
        return new IXR_Error(-32601, "server error. requested signature of $name method does not exist.");
      }
      
    }
    
    switch ($name) {
      case 'system.listMethods':
      case 'mt.listMethods':
      return array('array');
      
      case 'system.methodSignature':
      return array('array', 'string');
      
      case'system.methodHelp':
      return array('string', 'string');
      
      case 'system.multicall':
      return array('array', 'array');
      
      case 'methodExist':
      return array('boolean', 'string');
      
      case 'demo.addTwoNumbers':
      case 'sample.add':
      return array('int', 'int', 'int');
      
      case 'demo.sayHello':
      return array('string');
      
      default:
      return new IXR_Error(-32601, "server error. requested signature of $name method does not exist.");
    }
  }
  
  public function methodHelp($name) {
    return "I know nothing.";
  }
  
  public function multicall(array $items) {
    $result = array();
    $caller = TXMLRPC::i();
    foreach ($items as $item) {
      $r = $caller->call($item['methodName'], isset($item['params']) ? $item['params'] : null);
      if ($r instanceof IXR_Error) {
        $result[] = array(
        'faultCode' => $r->code,
        'faultName' => $r->message
        );
      } else {
        $result[] = $r;
      }
    }
    return $result;
  }
  
  public function methodExist($name) {
    $caller = TXMLRPC::i();
    return $caller->itemexists($name);
  }
  
  public function sayHello() {
    return 'Hello!';
  }
  
  public function addTwoNumbers($number1, $number2 ) {
    return $number1 + $number2;
  }
  
}//class
?>