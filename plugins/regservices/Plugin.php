<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\plugins\regservices;

use litepubl\comments\Form;
use litepubl\core\Context;
use litepubl\core\Event;
use litepubl\view\Theme;

class Plugin extends \litepubl\core\Items implements \litepubl\core\ResponsiveInterface
{

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'regservices' . DIRECTORY_SEPARATOR . 'index';
        $this->data['url'] = '/admin/regservice.htm';
        $this->data['title'] = '';
    }

    public function add(Service $service)
    {
        $this->lock();
        $this->items[$service->name] = get_class($service);
        $service->save();
        $this->unlock();
        $this->getApp()->cache->clear();
    }

    public function getWidget(): string
    {
        $cache = $this->getApp()->cache;
        $filename = 'regservices.' . Theme::i()->name;
        if ($result = $cache->getString($filename)) {
                return $result;
        }

        $result = $this->renderWidget();
        $cache->setString($filename, $result);
        return $result;
    }

    public function renderWidget(): string
    {
        $theme = Theme::i();
        $tml = $theme->templates['regservices.button'];
        $url = $this->getApp()->site->url . $this->url . $this->getApp()->site->q . 'id';
        $buttons = '';
        foreach ($this->items as $name => $classname) {
            $service = static ::iGet($classname);
            if ($service->valid()) {
                $buttons .= strtr(
                    $tml, array(
                    '$url' => $url,
                    '$name' => $name,
                    '$icon' => $service->icon,
                    '$title' => $service->title,
                    '&' => '&amp;',
                    )
                );
            }
        }

        return strtr(
            $theme->templates['regservices'], [
            '$title' => $this->title,
            '$button' => $buttons
            ]
        );
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;

        // hook for clien disabled cookies
        if (!isset($_GET['cookietest'])) {
            $backurl = !empty($_GET['backurl']) ? $_GET['backurl'] : (!empty($_GET['amp;backurl']) ? $_GET['amp;backurl'] : (isset($_COOKIE['backurl']) ? $_COOKIE['backurl'] : ''));
            if ($backurl) {
                setcookie('backurl', $backurl, time() + 8 * 3600, $this->getApp()->site->subdir . '/', false);
            }
            setcookie('litepubl_cookie_test', 'test', time() + 8000, $this->getApp()->site->subdir . '/', false);
            return $response->redir($context->request->url . '&cookietest=true');
        }

        if (!isset($_COOKIE['litepubl_cookie_test'])) {
            return $response->forbidden();
        }

        setcookie('litepubl_cookie_test', '', 0, $this->getApp()->site->subdir . '/', false);

        $id = empty($_GET['id']) ? 0 : $_GET['id'];
        if (!isset($this->items[$id])) {
            return $response->notfound();
        }

        $service = static ::iGet($this->items[$id]);
        if (!$service->valid()) {
            return $response->forbidden();
        }

        $url = $service->getAuthUrl();
        if (!$url) {
            return $response->forbidden();
        }

        $response->redir($url);
    }

    public function onComuser(Event $event)
    {
        $values = $event->values;
        //ignore $comfirmed, always return redirect
        $form = Form::i();
        if ($err = $form->processComUser($values)) {
            $values->result = $err;
            return;
        }

        $email = strtolower(trim($values['email']));
        $host = substr($email, strpos($email, '@') + 1);
        switch ($host) {
        case 'gmail.com':
            $name = 'google';
            break;


        case 'yandex.ru':
            $name = 'yandex';
            break;


        case 'mail.ru':
        case 'inbox.ru':
        case 'list.ru':
        case 'bk.ru':
            $name = 'mailru';
            break;


        default:
            return false;
        }

        if (!isset($this->items[$name])) {
            return false;
        }

        $service = static ::iGet($this->items[$name]);
        if (!$service->valid) {
            return false;
        }

        $service->sessdata['comuser'] = $values;
        $url = $service->getauthurl();
        if (!$url) {
            return false;
        }

        $event->result = $form->sendResult(
            $url, array(
            ini_get('session.name') => $service->session_id
            )
        );
    }
}
