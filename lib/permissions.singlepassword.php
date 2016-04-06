<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tsinglepassword extends tperm {
    private $password;
    private $checked;

    public function getheader($obj) {
        if (isset($obj->password) && ($p = $obj->password)) {
            return sprintf('<?php if (!%s::auth(%d, \'%s\')) return; ?>', get_class($this) , $this->id, static ::encryptpassword($p));
        }
    }

    public function hasperm($obj) {
        if (isset($obj->password) && ($p = $obj->password)) {
            return static ::authcookie(static ::encryptpassword($p));
        }
        return true;
    }

    public static function encryptpassword($p) {
        return md5(litepubl::$urlmap->itemrequested['id'] . litepubl::$secret . $p . litepubl::$options->solt);
    }

    public static function getcookiename() {
        return 'singlepwd_' . litepubl::$urlmap->itemrequested['id'];
    }

    public function checkpassword($p) {
        if ($this->password != static ::encryptpassword($p)) return false;
        $login = md5rand();
        $password = md5($login . litepubl::$secret . $this->password . litepubl::$options->solt);
        $cookie = $login . '.' . $password;
        $expired = isset($_POST['remember']) ? time() + 31536000 : time() + 8 * 3600;

        setcookie(static ::getcookiename() , $cookie, $expired, litepubl::$site->subdir . '/', false);
        $this->checked = true;
        return true;
    }

    public static function authcookie($p) {
        if (litepubl::$options->group == 'admin') return true;
        $cookiename = static ::getcookiename();
        $cookie = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
        if (($cookie != '') && strpos($cookie, '.')) {
            list($login, $password) = explode('.', $cookie);
            if ($password == md5($login . litepubl::$secret . $p . litepubl::$options->solt)) return true;
        }
        return false;
    }

    public static function auth($id, $p) {
        if (static ::authcookie($p)) return true;
        $self = static ::i($id);
        return $self->getform($p);
    }

    public function getform($p) {
        $this->password = $p;
        $page = tpasswordpage::i();
        $page->perm = $this;
        $result = $page->request(null);
        if ($this->checked) return true;

        switch ($result) {
            case 404:
                return litepubl::$urlmap->notfound404();

            case 403:
                return litepubl::$urlmap->forbidden();
        }

        $html = ttemplate::i()->request($page);
        eval('?>' . $html);
        return false;
    }

} //class