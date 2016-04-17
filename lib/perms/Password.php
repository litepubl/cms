<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\perms;

class Password extends Perm
 {

    protected function create() {
        parent::create();
        $this->adminclass = '\litepubl\admin\users\Password';
        $this->data['password'] = '';
        $this->data['solt'] = '';
    }

    public function getheader($obj) {
        if ($this->password) {
        return sprintf('<?php %s::i(%d)->auth(); ?>', get_class($this) , $this->id);
}return '';

return '';
    }

    public function hasperm($obj) {
        return $this->authcookie();
    }

    public function getcookiename() {
        return 'permpassword_' . $this->id;
    }

    public function setpassword($p) {
        $p = trim($p);
        if ($p) {
        $this->data['solt'] = md5uniq();
        $this->data['password'] = $this->hash($p, $this->solt);
        $this->save();
}
    }

public function hash($password, $solt) {
return md5($solt . litepubl::$secret . $password . litepubl::$options->solt);
}

    public function checkpassword($p) {
        if ($this->password != $this->hash($p, $this->solt)) {
return false;
}

        $solt = md5rand();
        $hash = $this->hash($this->password, $solt);
        $cookie = $solt . '.' . $hash;
        $expired = isset($_POST['remember']) ? time() + 31536000 : time() + 8 * 3600;

        setcookie($this->getcookiename() , $cookie, $expired, litepubl::$site->subdir . '/', false);
        return true;
    }

    public function authcookie() {
        if (litepubl::$options->group == 'admin') {
return true;
}

        $cookiename = $this->getcookiename();
        $cookie = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
        if (!$cookie || !strpos($cookie, '.')) {
return $this->redir();
}

        list($solt, $hash) = explode('.', $cookie);
return $hash == $this->hash($this->password, $solt);
    }

    public function auth() {
        if ($this->authcookie()) {
return true;
}

        return $this->redir();
    }

    public function redir() {
        $url = litepubl::$site->url . '/check-password.php' . litepubl::$site->q;
        $url.= "idperm=$this->id&backurl=" . urlencode(litepubl::$urlmap->url);
        litepubl::$urlmap->redir($url, 307);
    }

}
