<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tpermpassword extends tperm {

    protected function create() {
        parent::create();
        $this->adminclass = 'tadminpermpassword';
        $this->data['password'] = '';
        $this->data['login'] = '';
    }

    public function getheader($obj) {
        if ($this->password == '') return '';
        return sprintf('<?php %s::i(%d)->auth(); ?>', get_class($this) , $this->id);
    }

    public function hasperm($obj) {
        return $this->authcookie();
    }

    public function getcookiename() {
        return 'permpassword_' . $this->id;
    }

    public function setpassword($p) {
        $p = trim($p);
        if ($p == '') return false;
        $this->data['login'] = md5uniq();
        $this->data['password'] = md5($this->login . litepubl::$secret . $p . litepubl::$options->solt);
        $this->save();
    }

    public function checkpassword($p) {
        if ($this->password != md5($this->login . litepubl::$secret . $p . litepubl::$options->solt)) return false;
        $login = md5rand();
        $password = md5($login . litepubl::$secret . $this->password . litepubl::$options->solt);
        $cookie = $login . '.' . $password;
        $expired = isset($_POST['remember']) ? time() + 31536000 : time() + 8 * 3600;

        setcookie($this->getcookiename() , $cookie, $expired, litepubl::$site->subdir . '/', false);
        return true;
    }

    public function authcookie() {
        if (litepubl::$options->group == 'admin') return true;
        $cookiename = $this->getcookiename();
        $cookie = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
        if (($cookie == '') || !strpos($cookie, '.')) return $this->redir();
        list($login, $password) = explode('.', $cookie);
        if ($password == md5($login . litepubl::$secret . $this->password . litepubl::$options->solt)) return true;
        return false;
    }

    public function auth() {
        if ($this->authcookie()) return true;
        return $this->redir();
    }

    public function redir() {
        $url = litepubl::$site->url . '/check-password.php' . litepubl::$site->q;
        $url.= "idperm=$this->id&backurl=" . urlencode(litepubl::$urlmap->url);
        litepubl::$urlmap->redir($url, 307);
    }

} //class