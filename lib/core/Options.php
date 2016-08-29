<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\core;

use litepubl\Config;

/**
 * This is the class to storage common options
 *
 * @property       int $user
 * @property       string $email
 * @property       string $password
 * @property       string $solt
 * @property       bool $authenabled
 * @property       bool $usersenabled 
 * @property       bool $reguser
 * @property       bool $xxxcheck
 * @property       string $cookiehash
 * @property       int $cookieexpired
 * @property       bool $securecookie
 * @property       string $version
 * @property       string $language
 * @property       string $dateformat
 * @property       string $timezone
 * @property       string $mailer
 * @property       string $fromemail
 * @property       array $dbconfig
 * @property       bool $cache
 * @property       int $expiredcache
 * @property       bool $admincache
 * @property       bool $ob_cache
 * @property       int $filetime_offset
 * @property       int $perpage
 * @property       int $commentsperpage
 * @property       bool $commentpages
 * @property       bool $comments_invert_order
 * @property       bool $commentsdisabled
 * @property       string $comstatus
 * @property       bool $commentspool
 * @property       bool $pingenabled
 * @property       bool $echoexception
 * @property       bool $parsepost
 * @property       bool $hidefilesonpage
 * @property       bool $show_draft_post
 * @property       bool $show_file_perm
 * @property-write callable $changed
 * @method         array changed(array $params) triggered when option changed
 */

class Options extends Events
{
    use PoolStorageTrait;

    public $groupnames;
    public $parentgroups;
    public $group;
    public $idgroups;
    protected $idUser;
    protected $adminFlagChecked;
    public $gmt;

    protected function create()
    {
        parent::create();
        $this->basename = 'options';
        $this->addEvents('changed');
        unset($this->cache);
        $this->gmt = 0;
        $this->group = '';
        $this->idgroups = [];
        $this->addmap('groupnames', []);
        $this->addmap('parentgroups', []);
    }

    public function afterLoad()
    {
        parent::afterLoad();
        date_default_timezone_set($this->timezone);
        $this->gmt = date('Z');
        if (!defined('dbversion')) {
            define('dbversion', true);
        }
    }

    public function __set($name, $value)
    {
        try {
                parent::__set($name, $value);
        } catch(PropException $e) {
                $this->data[$name] = $value;
        }

        if (array_key_exists($name, $this->data)) {
            $this->save();
            $this->changed(['name' => $name, 'value' => $value]);
            $this->getApp()->cache->clear();
        }
    }

    public function delete(string $name): bool
    {
        if (array_key_exists($name, $this->data)) {
            unset($this->data[$name]);
            $this->save();
            return true;
        }

        return false;
    }

    public function getAdminFlag(): bool
    {
        if (is_null($this->adminFlagChecked)) {
            return $this->adminFlagChecked = $this->authenabled && isset($_COOKIE['litepubl_user_flag']) && ($_COOKIE['litepubl_user_flag'] == 'true');
        }

        return $this->adminFlagChecked;
    }

    public function setAdminFlag(bool $val)
    {
        $this->adminFlagChecked = $val;
    }

    public function resetUser()
    {
        $this->idUser = null;
    }

    public function getUser(): int
    {
        if (is_null($this->idUser)) {
            $this->idUser = $this->authenabled ? $this->authCookie() : 0;
        }

        return $this->idUser;
    }

    public function setUser(int $id)
    {
        $this->idUser = $id;
    }

    public function authCookie()
    {
        return $this->authcookies(isset($_COOKIE['litepubl_user_id']) ? (int)$_COOKIE['litepubl_user_id'] : 0, isset($_COOKIE['litepubl_user']) ? (string)$_COOKIE['litepubl_user'] : '');
    }

    public function authCookies(int $iduser, string $password): int
    {
        if ($iduser && $password) {
            $password = $this->hash($password);
            if (($password != $this->emptyhash) && $this->findUser($iduser, $password)) {
                $this->idUser = $iduser;
                $this->updategroup();
                return $iduser;
            }
        }

        return 0;
    }

    public function findUser(int $iduser, string $cookie): bool
    {
        if ($iduser == 1) {
            return $this->compareCookie($cookie);
        }
        if (!$this->usersenabled) {
            return 0;
        }

        $users = Users::i();
        try {
            $item = $users->getItem($iduser);
        } catch (\Exception $e) {
            return 0;
        }

        if ('hold' == $item['status']) {
            return 0;
        }
        return ($cookie == $item['cookie']) && (strtotime($item['expired']) > time());
    }

    private function compareCookie($cookie)
    {
        return !empty($this->cookiehash) && ($this->cookiehash == $cookie) && ($this->cookieexpired > time());
    }

    public function emailExists(string $email): int
    {
        if ($email && $this->authenabled) {
            if ($email == $this->email) {
                return 1;
            }

            if ($this->usersenabled) {
                return Users::i()->emailExists($email);
            }
        }

        return 0;
    }

    public function auth(string $email, string $password): int
    {
        if (!$this->authenabled) {
            return 0;
        }

        if (!$email && !$password) {
            return $this->authcookie();
        }

        return $this->authpassword($this->emailexists($email), $password);
    }

    public function authPassword(int $iduser, string $password): int
    {
        if ($iduser) {
            if ($iduser == 1) {
                if ($this->data['password'] != $this->hash($password)) {
                    return 0;
                }
            } else {
                if (!Users::i()->authPassword($iduser, $password)) {
                    return 0;
                }
            }

            $this->idUser = $iduser;
            $this->updateGroup();
            return $iduser;
        }

        return 0;
    }

    public function updateGroup()
    {
        if ($this->idUser == 1) {
            $this->group = 'admin';
            $this->idgroups = [1];
        } else {
            $user = Users::i()->getItem($this->idUser);
            $this->idgroups = $user['idgroups'];
            $this->group = count($this->idgroups) ? UserGroups::i()->items[$this->idgroups[0]]['name'] : '';
        }
    }

    public function can_edit($idauthor)
    {
        return ($idauthor == $this->user) || ($this->group == 'admin') || ($this->group == 'editor');
    }

    public function getpassword()
    {
        if ($this->user <= 1) {
            return $this->data['password'];
        }

        $users = Users::i();
        return $users->getValue($this->user, 'password');
    }

    public function changePassword(string $newpassword)
    {
        $this->data['password'] = $this->hash($newpassword);
        $this->save();
    }

    public function getDBPassword(): string
    {
        if (!$this->data['dbconfig']['crypt']) {
                return $this->data['dbconfig']['password'];
        } elseif ($this->data['dbconfig']['crypt'] == Crypt::METHOD) {
            return Crypt::decode($this->data['dbconfig']['password'], $this->solt . Config::$secret);
        } else {
            $this->error('Cant decrypt database password');
        }
    }

    public function setDBPassword(string $password)
    {
        if (!$this->data['dbconfig']['crypt']) {
            $this->data['dbconfig']['password'] = $password;
        } elseif ($this->data['dbconfig']['crypt'] == Crypt::METHOD) {
            $this->data['dbconfig']['password'] = Crypt::encode($password, $this->solt . Config::$secret);
        } else {
            $this->error('Cant encrypt database password');
        }

        $this->save();
    }

    public function logout()
    {
        $this->setcookies('', 0);
    }

    public function setCookie(string $name, string $value, int $expired)
    {
        setcookie($name, $value, $expired, $this->getApp()->site->subdir . '/', false, '', $this->securecookie);
    }

    public function setcookies($cookie, $expired)
    {
        $this->setcookie('litepubl_user_id', $cookie ? $this->idUser : '', $expired);
        $this->setcookie('litepubl_user', $cookie, $expired);
        $this->setcookie('litepubl_user_flag', $cookie && ('admin' == $this->group) ? 'true' : '', $expired);

        if ($this->idUser == 1) {
            $this->saveCookie($cookie, $expired);
        } elseif ($this->idUser) {
            Users::i()->setCookie($this->idUser, $cookie, $expired);
        }
    }

    public function setTimeZone(string $value)
    {
        if (!isset($this->data['timezone']) || ($this->timezone != $value)) {
            $this->data['timezone'] = $value;
            date_default_timezone_set($this->timezone);
            $this->gmt = date('Z');
            $this->save();
        }
    }

    public function saveCookie($cookie, $expired)
    {
        $this->data['cookiehash'] = $cookie ? $this->hash($cookie) : '';
        $this->cookieexpired = $expired;
        $this->save();
    }

    public function hash(string $s): string
    {
        return Str::baseMD5($s . $this->solt . Config::$secret);
    }

    public function setSolt(string $value)
    {
        $this->data['solt'] = $value;
                $this->data['emptyhash'] = $this->hash('');
    }

    public function inGroup(string $groupname): bool
    {
        //admin has all rights
        if ($this->user == 1) {
            return true;
        }

        if (in_array($this->groupnames['admin'], $this->idgroups)) {
            return true;
        }

        if (!$groupname) {
            return true;
        }

        $groupname = trim($groupname);
        if ($groupname == 'admin') {
            return false;
        }

        if (!isset($this->groupnames[$groupname])) {
            $this->error(sprintf('The "%s" group not found', $groupname));
        }

        $idgroup = $this->groupnames[$groupname];
        return in_array($idgroup, $this->idgroups);
    }

    public function inGroups(array $idgroups): bool
    {
        if ($this->inGroup('admin')) {
            return true;
        }

        return count(array_intersect($this->idgroups, $idgroups));
    }

    public function hasGroup(string $groupname): bool
    {
        if ($this->inGroup($groupname)) {
            return true;
        }
        // if group is children of user groups
        $idgroup = $this->groupnames[$groupname];
        if (!isset($this->parentgroups[$idgroup])) {
            return false;
        }
        return count(array_intersect($this->idgroups, $this->parentgroups[$idgroup]));
    }
}
