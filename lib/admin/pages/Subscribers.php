<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\admin\pages;

use litepubl\admin\Menus;
use litepubl\comments\Subscribers as SubscriberItems;
use litepubl\core\Context;
use litepubl\core\Str;
use litepubl\core\UserOptions;
use litepubl\core\Users;
use litepubl\post\Posts;
use litepubl\view\Args;
use litepubl\view\Lang;

class Subscribers extends Form
{
    private $iduser;
    private $newreg;

    protected function create()
    {
        parent::create();
        $this->section = 'subscribers';
        $this->iduser = false;
        $this->newreg = false;
    }

    public function request(Context $context)
    {
        $context->response->cache = false;
        $app = $this->getApp();
        if (!($this->iduser = $app->options->user)) {
            //trick - hidden registration of comuser. Auth by get
            $users = Users::i();
            if (isset($_GET['auth']) && ($cookie = trim($_GET['auth']))) {
                if (($this->iduser = $users->findcookie($cookie)) && $app->options->reguser) {
                    if ('comuser' == $users->getvalue($this->iduser, 'status')) {
                        // bingo!
                        $this->newreg = true;
                        $item = $users->getitem($this->iduser);
                        $item['status'] = 'approved';
                        $item['password'] = '';
                        $item['idgroups'] = 'commentator';

                        $cookie = Str::md5Uniq();
                        $expired = time() + 31536000;

                        $item['cookie'] = $this->getApp()->options->hash($cookie);
                        $item['expired'] = Str::sqlDate($expired);
                        $users->edit($this->iduser, $item);

                        $app->options->user = $this->iduser;
                        $app->options->updategroup();

                        $app->options->setcookie('litepubl_user_id', $this->iduser, $expired);
                        $app->options->setcookie('litepubl_user', $cookie, $expired);
                    } else {
                        $this->iduser = false;
                    }
                }
            }
        }

        if (!$this->iduser) {
            $url = $app->site->url . '/admin/login/' . $app->site->q . 'backurl=' . rawurlencode('/admin/subscribers/');
            return $response->redir($url);
        }

        if ('hold' == Users::i()->getvalue($this->iduser, 'status')) {
            return $response->forbidden();
        }

        return parent::request($context);
    }

    public function getIdSchema(): int
    {
        return Schemes::i()->defaults['admin'];
    }

    public function getHead(): string
    {
        $result = parent::gethead();
        $result.= Menus::i()->heads;
        return $result;
    }

    public function getContent(): string
    {
        $result = '';
        $admin = $this->admintheme;
        $lang = Lang::admin();
        $args = new Args();
        if ($this->newreg) {
            $result.= $admin->h($lang->newreg);
        }

        $subscribers = SubscriberItems::i();
        $items = $subscribers->getposts($this->iduser);
        if (count($items) == 0) {
            return $admin->h($lang->nosubscribtions);
        }
        Posts::i()->loaditems($items);
        $args->default_subscribe = UserOptions::i()->getvalue($this->iduser, 'subscribe') == 'enabled';
        $args->formtitle = Users::i()->getvalue($this->iduser, 'email') . ' ' . $lang->formhead;

        $tb = new Table();
        $tb->setposts(array(
            array(
                $lang->post,
                '<a href="$site.url$post.url" title="$post.title">$post.title</a>'
            )
        ));

        return $admin->form('[checkbox=default_subscribe]' . $tb->build($items) , $args);
    }

    public function processForm()
    {
        UserOptions::i()->setvalue($this->iduser, 'subscribe', isset($_POST['default_subscribe']) ? 'enabled' : 'disabled');

        $subscribers = SubscriberItems::i();
        foreach ($_POST as $name => $value) {
            if (Str::begin($name, 'checkbox-')) {
                $subscribers->remove((int)$value, $this->iduser);
            }
        }

        return $this->admin->h(Lang::admin()->unsubscribed);
    }

}

