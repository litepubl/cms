<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tusersman extends tdata {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function add(array $values) {
    $users = tusers::i();
    $email = trim($values['email']);
    if ( $users->emailexists($email)) return false;
    $groups = tusergroups::i();
    if (isset($values['idgroups'])) {
      $idgroups = $this->cleangroups($values['idgroups']);
      if (count($idgroups) == 0) $idgroups = $groups->defaults;
    } else {
      $idgroups = $groups->defaults;
    }
    
    $password = empty($values['password']) ? md5uniq() : $values['password'];
    
    $item = array(
    'email' => $email,
    'name' =>isset($values['name']) ? trim($values['name']) : '',
    'website' => isset($values['website']) ? trim($values['website']) : '',
    'password' => litepublisher::$options->hash($email . $password),
    'cookie' =>  md5uniq(),
    'expired' => sqldate(),
    'idgroups' => implode(',', $idgroups),
    'trust' => 0,
    'status' => isset($values['status']) ? $values['status'] : 'approved',
    );
    
    $id = $users->db->add($item);
    $item['idgroups'] = $idgroups;
    $users->items[$id] = $item;
    $users->setgroups($id, $item['idgroups']);
    if ('approved' == $item['status']) tuserpages::i()->add($id);
    $users->added($id);
    return $id;
  }
  
  public function edit($id, array $values) {
    $users = tusers::i();
    if (!$users->itemexists($id)) return false;
    $item = $users->getitem($id);
    foreach ($item as $k => $v) {
      if (!isset($values[$k])) continue;
      switch ($k) {
        case 'password':
        if ($values['password'] != '') {
          $item['password'] = litepublisher::$options->hash($values['email'] . $values['password']);
        }
        break;
        
        case 'idgroups':
        $groups = tusergroups::i();
        $item['idgroups'] = $this->cleangroups($values['idgroups']);
        break;
        
        default:
        $item[$k] = trim($values[$k]);
      }
    }
    
    $users->items[$id] = $item;
    $item['id'] = $id;
    
    $users->setgroups($id, $item['idgroups']);
    $item['idgroups'] = implode(',', $item['idgroups']);
    $users->db->updateassoc($item);
    
    $pages = tuserpages::i();
    if (isset($values['status']) && ('approved' == $values['status']) && ($item['status'] != $values['status'])) {
      if ($pages->itemexists($id)) {
        if ($pages->createpage) $pages->addpage($id);
      } else {
        $pages->add($id);
      }
    }
    
    $pages->edit($id, $values);
    return true;
  }
  
  public function cleangroups($v) {
    if (is_array($v)) return $this->checkgroups(array_unique($v));
    
    if(is_string($v)) {
      $v = trim($v);
      if (strpos($v, ',')) return $this->checkgroups(explode(',', $v));
    }
    if ($id = $this->cleangroup($v)) return array($id);
  }
  
  public function checkgroups(array $a) {
    $result = array();
    foreach ($a as $val) {
      if ($id = $this->cleangroup($val)) $result[] = $id;
    }
    
    return array_unique($result);
  }
  
  public function cleangroup($v) {
    if (is_string($v)) $v = trim($v);
    if (is_numeric($v)) {
      $id = (int) $v;
      if (tusergroups::i()->itemexists($id)) return $id;
    } else {
      return tusergroups::i()->getidgroup($v);
    }
    return false;
  }
  
}//class