<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ulogin extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('added', 'onadd', 'onphone');
    $this->table = 'ulogin';
    $this->data['url'] = '/admin/ulogin.php';
    $this->data['panel'] = '';
    $this->data['button'] = '';
    $this->data['nets'] = array();
  }
  
  public function add($id, $service, $uid) {
    if (($id == 0) || ($service == '') || ($uid == '')) return;
    if (!in_array($service, $this->data['nets'])) {
      $this->data['nets'][] = $service;
      $this->save();
      tdbmanager::i()->add_enum($this->table, 'service', $service);
    }
    
    $this->db->insert(array(
    'id' => $id,
    'service' => $service,
    'uid' => $uid
    ));
    
    $this->added($id, $service);
    return $id;
  }
  
  public function find($service, $uid) {
    return $this->db->findid('service = '. dbquote($service) . ' and uid = ' . dbquote($uid));
  }
  
  public function userdeleted($id) {
    $this->db->delete("id = $id");
  }
  
  public function request($arg) {
    $this->cache = false;
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');
    
    $token = isset($_POST['token']) ? $_POST['token'] : (isset($_GET['token']) ? $_GET['token'] : '');
    if (!$token) {
      //try fix ulogin bug double symbol ?
      $uri = $_SERVER['REQUEST_URI'];
      if (substr_count($uri, '?') <= 1)  return 403;
      $q = substr($uri, strpos($uri, '?') +1);
      $q = str_replace('?', '&', $q);
      parse_str($q, $_GET);
      $token = isset($_GET['token']) ? $_GET['token'] : '';
      if (!$token) return 403;
    }
    
    if (!($cookies = $this->auth($token))) return 403;
    
    if (!empty($_GET['backurl'])) {
      $backurl = $_GET['backurl'];
    } elseif (!empty($_COOKIE['backurl'])) {
      $backurl = $_COOKIE['backurl'];
    } else {
      $user = tusers::i()->getitem($cookies['id']);
      $backurl =  tusergroups::i()->gethome($user['idgroups'][0]);
    }
    
    if (! (int) tusers::i()->db->getvalue($cookies['id'], 'phone')) {
      if ($url = $this->onphone($backurl))     return litepublisher::$urlmap->redir($url);
    }
    
    setcookie('backurl', '', 0, litepublisher::$site->subdir, false);
    return litepublisher::$urlmap->redir($backurl);
  }
  
  public function auth($token) {
    if (!($s = http::get('http://ulogin.ru/token.php?token=' . $token . '&host=' . $_SERVER['HTTP_HOST']))) return false;
    if (!($info = json_decode($s, true))) return false;
    if (isset($info['error']) || !isset($info['network'])) return false;
    
    $name =!empty($info['first_name']) ? $info['first_name'] : '';
    $name .= !empty($info['last_name']) ? ' ' . $info['last_name'] : '';
    if (!$name && !empty($info['nickname'])) $name = $info['nickname'];
    
    $uid = !empty($info['uid']) ? $info['uid'] :
    (!empty($info['id']) ? $info['id'] :
    (!empty($info['identity']) ? $info['identity'] :
    (!empty($info['profile']) ? $info['profile'] : '')));
    if (strlen($uid) >= 22) $uid = basemd5($uid);
    
    $phone = !empty($info['phone']) ? self::filterphone($info['phone']) : false;
    
    $newreg = false;
    $users = tusers::i();
    if (!empty($info['email'])) {
      if ($id = $users->emailexists($info['email'])) {
        $user = $users->getitem($id);
        if ($user['status'] == 'comuser') $users->approve($id);
        if ($phone && empty($user['phone'])) $users->setvalue($id, 'phone', $phone);
      } elseif (litepublisher::$options->reguser) {
        $newreg = true;
        $id = $users->add(array(
        'email' => $info['email'],
        'name' => $name,
        'website' => empty($info['profile']) ? '' : tcontentfilter::clean_website($info['profile']),
        ));
        if ($phone) $users->db->setvalue($id, 'phone', $phone);
        if ($uid) {
          $this->add($id, $info['network'], $uid);
        }
      } else {
        //registration disabled
        return false;
      }
    } else {
      if ($uid) {
        if ($id = $this->find($info['network'], $uid)){
          //nothing
        } elseif (litepublisher::$options->reguser) {
          $newreg = true;
          $id = $users->add(array(
          'email' => '',
          'name' => $name,
          'website' => empty($info['profile']) ? '' : tcontentfilter::clean_website($info['profile']),
          ));
          $users->approve($id);
          if ($phone) $users->db->setvalue($id, 'phone', $phone);
          $this->add($id, $info['network'], $uid);
        } else {
          //registration disabled
          return false;
        }
      } else {
        //nothing found and hasnt email or uid
        return false;
      }
    }
    
    $expired = time() + 31536000;
    $cookie = md5uniq();
    litepublisher::$options->user = $id;
    litepublisher::$options->updategroup();
    litepublisher::$options->setcookies($cookie, $expired);
    if (litepublisher::$options->ingroup('admin')) setcookie('litepubl_user_flag', 'true', $expired, litepublisher::$site->subdir . '/', false);
    
    setcookie('litepubl_regservice', $info['network'], $expired, litepublisher::$site->subdir . '/', false);
    $this->onadd($id, $info, $newreg);
    
    return array(
    'id' => $id,
    'pass' => $cookie,
    'regservice' => $info['network']
    );
  }
  
  public function ulogin_auth(array $args) {
    if (!isset($args['token']) || (!($token = $args['token']))) return $this->error('Invalide token', 403);
    $result = $this->auth($token);
    if (!$result) $this->error('Not authorized', 403);
    return $result;
  }
  
  public function addpanel($s, $panel) {
    $open = '<!--ulogin-->';
    $close = '<!--/ulogin-->';
    $s = $this->deletepanel($s);
    return $open . $panel . $close;
  }
  
  public function deletepanel($s) {
    $open = '<!--ulogin-->';
    $close = '<!--/ulogin-->';
    if (false !== ($i = strpos($s, $open))) {
      if ($j = strpos($s, $close)) {
        $s = trim(substr($s, 0, $i)) .
        trim(substr($s, $j + strlen($close) + 1));
      } else {
        $s = trim(substr($s, 0, $i));
      }
    }
    return $s;
  }
  
  public function check_logged(array $args) {
    if (litepublisher::$options->authcookies($args['litepubl_user_id'], $args['litepubl_user'])) {
      return array(
      'logged' => true
      );
    } else {
      return array('error' => array(
      'message' => 'Not logged',
      'code' => 403
      ));
    }
  }
  
  public static function filterphone($phone) {
    $phone = trim(str_replace(array(' ', '+', '=', '-', '_', '(', ')', '.'), '', trim($phone)));
    if (strlen($phone) && ($phone[0] == '9')) $phone = '7' . $phone;
    return (float) $phone;
  }
  
}//class