<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\perms;

use litepubl\Config;
use litepubl\core\Response;
use litepubl\core\Str;

class Password extends Perm
{

    protected function create()
    {
        parent::create();
        $this->adminclass = '\litepubl\admin\users\Password';
        $this->data['password'] = '';
        $this->data['solt'] = '';
    }

    public function setResponse(Response $response, $obj)
    {
        if ($this->password) {
            $response->body.= sprintf('<?php %s::i(%d)->auth(); ?>', get_class($this), $this->id);
        }

        return '';
    }

    public function hasPerm($obj): bool
    {
        return $this->authcookie();
    }

    public function getCookiename()
    {
        return 'permpassword_' . $this->id;
    }

    public function setPassword($p)
    {
        $p = trim($p);
        if ($p) {
            $this->data['solt'] = Str::md5Uniq();
            $this->data['password'] = $this->hash($p, $this->solt);
            $this->save();
        }
    }

    public function hash($password, $solt)
    {
        return md5($solt . Config::$secret . $password . $this->getApp()->options->solt);
    }

    public function checkpassword($p)
    {
        if ($this->password != $this->hash($p, $this->solt)) {
            return false;
        }

        $solt = Str::md5Rand();
        $hash = $this->hash($this->password, $solt);
        $cookie = $solt . '.' . $hash;
        $expired = isset($_POST['remember']) ? time() + 31536000 : time() + 8 * 3600;

        setcookie($this->getcookiename(), $cookie, $expired, $this->getApp()->site->subdir . '/', false);
        return true;
    }

    public function authcookie()
    {
        if ($this->getApp()->options->group == 'admin') {
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

    public function auth()
    {
        if ($this->authcookie()) {
            return true;
        }

        return $this->redir();
    }

    public function redir()
    {
        $url = $this->getApp()->site->url . '/check-password.php' . $this->getApp()->site->q;
        $url.= "idperm=$this->id&backurl=" . urlencode($this->getApp()->router->url);
        $this->getApp()->redirExit($url);
    }
}
