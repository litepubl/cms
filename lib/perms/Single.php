<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\perms;

use litepubl\Config;
use litepubl\core\Context;
use litepubl\core\ErrorPages;
use litepubl\core\Request;
use litepubl\core\Response;
use litepubl\core\Str;
use litepubl\view\MainView;

class Single extends Perm
{
    private $password;
    private $checked;

    public function setResponse(Response $response, $obj)
    {
        if (isset($obj->password) && ($p = $obj->password)) {
            $response->body.= sprintf('<?php if (!%s::auth(%d, \'%s\')) return; ?>', get_class($this), $this->id, static ::encryptpassword($p));
        }
    }

    public function hasPerm($obj): bool
    {
        if (isset($obj->password) && ($p = $obj->password)) {
            return static ::authcookie(static ::encryptpassword($p));
        }

        return true;
    }

    public static function encryptPassword(string $p): string
    {
        $app = static::getAppInstance();
        return md5($app->context->itemRoute['id'] . Config::$secret . $p . $app->options->solt);
    }

    public static function hash($password, $solt)
    {
        return md5($solt . Config::$secret . $password . static::getAppInstance()->options->solt);
    }

    public static function getCookiename()
    {
        return 'singlepwd_' . static::getAppInstance()->router->item['id'];
    }

    public function checkPassword($p)
    {
        if ($this->password != static ::encryptpassword($p)) {
            return false;
        }

        $solt = Str::md5Rand();
        $hash = static ::hash($this->password, $solt);
        $cookie = $solt . '.' . $hash;
        $expired = isset($_POST['remember']) ? time() + 31536000 : time() + 8 * 3600;

        setcookie(static ::getcookiename(), $cookie, $expired, $this->getApp()->site->subdir . '/', false);
        $this->checked = true;
        return true;
    }

    public static function authcookie($password)
    {
        if (static::getAppInstance()->options->group == 'admin') {
            return true;
        }

        $cookiename = static ::getcookiename();
        $cookie = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
        if ($cookie && strpos($cookie, '.')) {
            list($solt, $hash) = explode('.', $cookie);
            return $hash == static ::hash($password, $solt);
        }

        return false;
    }

    public static function auth($id, $p)
    {
        if (static ::authcookie($p)) {
            return true;
        }

        return static ::i($id)->getform($p);
    }

    public function getForm($p)
    {
        $this->password = $p;
        $page = Page::i();
        $page->perm = $this;
        $context = new Context(new Request(), new Response());
        $page->request($context);
        if ($this->checked) {
            return true;
        }

        switch ($context->response->status) {
            case 404:
                $errorPages = new ErrorPages();
                $errorPages->notfound();
                break;


            case 403:
                $errorPages = new ErrorPages();
                $errorPages->forbidden();
                break;


            default:
                MainView::i()->render($context);
                $context->response->send();
        }

        return false;
    }
}
