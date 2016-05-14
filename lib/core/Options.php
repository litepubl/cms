<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;
use litepubl\Config;

class Options extends Events
{
use PoolStorageTrait;

    public $groupnames;
    public $parentgroups;
    public $group;
    public $idgroups;
    protected $_user;
    protected $adminFlagChecked;
    public $gmt;
    public $errorlog;

    protected function create() {
        parent::create();
        $this->basename = 'options';
        $this->addevents('changed', 'perpagechanged');
        unset($this->cache);
        $this->gmt = 0;
        $this->errorlog = '';
        $this->group = '';
        $this->idgroups = array();
        $this->addmap('groupnames', array());
        $this->addmap('parentgroups', array());
    }

    public function afterLoad() {
        parent::afterload();
        date_default_timezone_set($this->timezone);
        $this->gmt = date('Z');
        if (!defined('dbversion')) define('dbversion', true);
    }

    public function __set($name, $value) {
        if (in_array($name, $this->eventnames)) {
            $this->addevent($name, $value['class'], $value['func']);
            return true;
        }

        if (method_exists($this, $set = 'set' . $name)) {
            $this->$set($value);
            return true;
        }

        if (!array_key_exists($name, $this->data) || ($this->data[$name] != $value)) {
            $this->data[$name] = $value;
            if ($name == 'solt') $this->data['emptyhash'] = $this->hash('');
            $this->save();
            $this->dochanged($name, $value);
        }
        return true;
    }

    private function doChanged($name, $value) {
        if ($name == 'perpage') {
            $this->perpagechanged();
$this->getApp()->cache->clear();
        } elseif ($name == 'cache') {
$this->getApp()->cache->clear();
        } else {
            $this->changed($name, $value);
        }
    }

    public function delete($name) {
        if (array_key_exists($name, $this->data)) {
            unset($this->data[$name]);
            $this->save();
        }
    }

    public function getAdminFlag() {
        if (is_null($this->adminFlagChecked)) {
            return $this->adminFlagChecked = $this->authenabled && isset($_COOKIE['litepubl_user_flag']) && ($_COOKIE['litepubl_user_flag'] == 'true');
        }

        return $this->adminFlagChecked;
    }

    public function setAdminFlag($val) {
        $this->adminFlagChecked = $val;
    }

    public function getuser() {
        if (is_null($this->_user)) {
            $this->_user = $this->authenabled ? $this->authcookie() : false;
        }

        return $this->_user;
    }

    public function setuser($id) {
        $this->_user = $id;
    }

    public function authCookie() {
        return $this->authcookies(isset($_COOKIE['litepubl_user_id']) ? (int)$_COOKIE['litepubl_user_id'] : 0, isset($_COOKIE['litepubl_user']) ? (string)$_COOKIE['litepubl_user'] : '');
    }

    public function authCookies($iduser, $password) {
        if (!$iduser || !$password) return false;
        $password = $this->hash($password);
        if ($password == $this->emptyhash) return false;
        if (!$this->finduser($iduser, $password)) return false;

        $this->_user = $iduser;
        $this->updategroup();
        return $iduser;
    }

    public function findUser($iduser, $cookie) {
        if ($iduser == 1) return $this->compare_cookie($cookie);
        if (!$this->usersenabled) return false;

        $users = Users::i();
        try {
            $item = $users->getitem($iduser);
        }
        catch(\Exception $e) {
            return false;
        }

        if ('hold' == $item['status']) return false;
        return ($cookie == $item['cookie']) && (strtotime($item['expired']) > time());
    }

    private function compare_cookie($cookie) {
        return !empty($this->cookiehash) && ($this->cookiehash == $cookie) && ($this->cookieexpired > time());
    }

    public function emailExists($email) {
        if (!$email) return false;
        if (!$this->authenabled) return false;
        if ($email == $this->email) return 1;
        if (!$this->usersenabled) return false;
        return Users::i()->emailexists($email);
    }

    public function auth($email, $password) {
        if (!$this->authenabled) return false;
        if (!$email && !$password) return $this->authcookie();
        return $this->authpassword($this->emailexists($email) , $password);
    }

    public function authPassword($iduser, $password) {
        if (!$iduser) return false;
        if ($iduser == 1) {
            if ($this->data['password'] != $this->hash($password)) return false;
        } else {
            if (!Users::i()->authpassword($iduser, $password)) return false;
        }

        $this->_user = $iduser;
        $this->updategroup();
        return $iduser;
    }

    public function updateGroup() {
        if ($this->_user == 1) {
            $this->group = 'admin';
            $this->idgroups = array(
                1
            );
        } else {
            $user = Users::i()->getitem($this->_user);
            $this->idgroups = $user['idgroups'];
            $this->group = count($this->idgroups) ? UserGroups::i()->items[$this->idgroups[0]]['name'] : '';
        }
    }

    public function can_edit($idauthor) {
        return ($idauthor == $this->user) || ($this->group == 'admin') || ($this->group == 'editor');
    }

    public function getpassword() {
        if ($this->user <= 1) {
return $this->data['password'];
}

        $users = Users::i();
        return $users->getvalue($this->user, 'password');
    }

    public function changePassword($newpassword) {
        $this->data['password'] = $this->hash($newpassword);
        $this->save();
    }

    public function getDBPassword() {
        if (function_exists('mcrypt_encrypt')) {
            return static ::decrypt($this->data['dbconfig']['password'], $this->solt . Config::$secret);
        } else {
            return str_rot13(base64_decode($this->data['dbconfig']['password']));
        }
    }

    public function setDBPassword($password) {
        if (function_exists('mcrypt_encrypt')) {
            $this->data['dbconfig']['password'] = static ::encrypt($password, $this->solt . Config::$secret);
        } else {
            $this->data['dbconfig']['password'] = base64_encode(str_rot13($password));
        }

        $this->save();
    }

    public function logout() {
        $this->setcookies('', 0);
    }

    public function setcookie($name, $value, $expired) {
        setcookie($name, $value, $expired, $this->getApp()->site->subdir . '/', false, '', $this->securecookie);
    }

    public function setcookies($cookie, $expired) {
        $this->setcookie('litepubl_user_id', $cookie ? $this->_user : '', $expired);
        $this->setcookie('litepubl_user', $cookie, $expired);
        $this->setcookie('litepubl_user_flag', $cookie && ('admin' == $this->group) ? 'true' : '', $expired);

        if ($this->_user == 1) {
            $this->save_cookie($cookie, $expired);
        } else if ($this->_user) {
            Users::i()->setcookie($this->_user, $cookie, $expired);
        }
    }

    public function Getinstalled() {
        return isset($this->data['email']);
    }

    public function settimezone($value) {
        if (!isset($this->data['timezone']) || ($this->timezone != $value)) {
            $this->data['timezone'] = $value;
            $this->save();
            date_default_timezone_set($this->timezone);
            $this->gmt = date('Z');
        }
    }

    public function save_cookie($cookie, $expired) {
        $this->data['cookiehash'] = $cookie ? $this->hash($cookie) : '';
        $this->cookieexpired = $expired;
        $this->save();
    }

    public function hash($s) {
        return Str::basemd5((string)$s . $this->solt . Config::$secret);
    }

    public function inGroup($groupname) {
        //admin has all rights
        if ($this->user == 1) return true;
        if (in_array($this->groupnames['admin'], $this->idgroups)) return true;
        if (!$groupname) return true;
        $groupname = trim($groupname);
        if ($groupname == 'admin') return false;
        if (!isset($this->groupnames[$groupname])) $this->error(sprintf('The "%s" group not found', $groupname));
        $idgroup = $this->groupnames[$groupname];
        return in_array($idgroup, $this->idgroups);
    }

    public function inGroups(array $idgroups) {
        if ($this->ingroup('admin')) return true;
        return count(array_intersect($this->idgroups, $idgroups));
    }

    public function hasGroup($groupname) {
        if ($this->ingroup($groupname)) return true;
        // if group is children of user groups
        $idgroup = $this->groupnames[$groupname];
        if (!isset($this->parentgroups[$idgroup])) return false;
        return count(array_intersect($this->idgroups, $this->parentgroups[$idgroup]));
    }

}