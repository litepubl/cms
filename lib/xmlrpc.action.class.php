<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCAction extends titems {
  public $actions;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'openaction';
    $this->addmap('actions', array());
  }
  
  public function send($id, $from, $name, $args) {
    if (!isset($this->items[$name])) return new IXR_Error(404, "The $name action not registered");
    // confirm callback
    $Client  = new IXR_Client($from);
    if ($Client->query('litepublisher.action.confirm', $id, $from, $name, $args)) {
      $confirmed = $Client->getResponse();
    } else {
      $confirmed = false;
    }
    if (!$confirmed) return new IXR_Error(403, 'Action not confirmed');
    return $this->doaction($name, $args);
  }
  
  public function confirm($id, $to, $name, $args) {
    $this->DeleteExpired();
    if (!isset($this->actions[$id])) return new IXR_Error(403, 'Action not found');
    if ($to != litepublisher::$site->url . '/rpc.xml') return new IXR_Error(403, 'Bad xmlrpc server');
    return true;
  }
  
  private function doaction($name, $args) {
    if (!is_array($args)) $args = array(0 => $args);
    $class = $this->items[$name]['class'];
    $func = $this->items[$name]['func'];
    if (empty($class)) {
      if (!function_exists($func)) {
        unset($this->items[$name]);$this->Save();
        return new IXR_Error(404, 'The requested function not found');
      }
      //return $func($arg);
      try {
        return call_user_func_array($func, $args);
      } catch (Exception $e) {
        return new IXR_Error($e->getCode(), $e->getMessage());
      }
      
    } else {
      if (!class_exists($class)) {
        unset($this->items[$name]);
        $this->save();
        return new IXR_Error(404, 'The requested class not found');
      }
      $obj = getinstance($class);
      //return $obj->$func($arg);
      try {
        return call_user_func_array(array($obj, $func), $args);
      } catch (Exception $e) {
        return new IXR_Error($e->getCode(), $e->getMessage());
      }
    }
  }
  
  
  public function __call($name, $args) {
    if (isset($this->items[$name])) {
      return $this->callaction($name, $args[0], $args[1]);
    }
    return parent::__call($name, $args);
  }
  
  public function callaction($name, $to, $args) {
    $this->lock();
    $this->DeleteExpired();
    $id = md5uniq();
    $this->actions[$id] = array(
    'date' => time(),
    'to' => $to,
    'name' => $name,
    'args' => $args
    );
    $this->unlock();
    
    $Client  = new IXR_Client($to);
    if ($Client->query('litepublisher.action.send', $id, litepublisher::$site->url . '/rpc.xml', $name, $args)) {
      return $Client->getResponse();
    }
    return false;
  }
  
  private function DeleteExpired() {
    $this->lock();
    $expired = time() - litepublisher::$options->expiredcache;
    foreach ($this->actions as $id => $item) {
      if ($item['date'] < $expired) unset($this->actions[$id]);
    }
    $this->unlock();
  }
  
  public function add($name, $class, $func) {
    $this->items[$name] = array(
    'class' => $class,
    'func' => $func
    );
    $this->save();
  }
  
  public function deleteclass($class) {
    foreach ($this->items as $id => $item) {
      if ($class == $item['class']) unset($this->items[$id]);
    }
    $this->save();
  }
  
}//class