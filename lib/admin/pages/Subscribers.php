<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\pages;
use litepubl\admin\Menus;
use litepubl\core\Users;
use litepubl\core\UserOptions;
use litepubl\comments\Subscribers as SubscriberItems;
use litepubl\post\Posts;
use litepubl\view\Lang;

class Subscribers extends Form
{
    private $iduser;
    private $newreg;

    protected function create() {
        parent::create();
        $this->section = 'subscribers';
        $this->iduser = false;
        $this->newreg = false;
    }

    public function request($arg) {
        $this->cache = false;
        if (!($this->iduser = litepubl::$options->user)) {
            //trick - hidden registration of comuser. Auth by get
            $users = Users::i();
            if (isset($_GET['auth']) && ($cookie = trim($_GET['auth']))) {
                if (($this->iduser = $users->findcookie($cookie)) && litepubl::$options->reguser) {
                    if ('comuser' == $users->getvalue($this->iduser, 'status')) {
                        // bingo!
                        $this->newreg = true;
                        $item = $users->getitem($this->iduser);
                        $item['status'] = 'approved';
                        $item['password'] = '';
                        $item['idgroups'] = 'commentator';

                        $cookie = md5uniq();
                        $expired = time() + 31536000;

                        $item['cookie'] = litepubl::$options->hash($cookie);
                        $item['expired'] = sqldate($expired);
                        $users->edit($this->iduser, $item);

                        litepubl::$options->user = $this->iduser;
                        litepubl::$options->updategroup();

                        litepubl::$options->setcookie('litepubl_user_id', $this->iduser, $expired);
                        litepubl::$options->setcookie('litepubl_user', $cookie, $expired);
                    } else {
                        $this->iduser = false;
                    }
                }
            }
        }

        if (!$this->iduser) {
            $url = litepubl::$site->url . '/admin/login/' . litepubl::$site->q . 'backurl=' . rawurlencode('/admin/subscribers/');
            return litepubl::$urlmap->redir($url);
        }

        if ('hold' == Users::i()->getvalue($this->iduser, 'status')) return 403;
        return parent::request($arg);
    }

    public function gethead() {
        $result = parent::gethead();
        $result.= Menus::i()->heads;
        return $result;
    }

    public function getcontent() {
        $result = '';
        $admin = $this->admintheme;
        $lang = tlocal::admin();
        $args = new targs();
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

        $tb = new tablebuilder();
        $tb->setposts(array(
            array(
                $lang->post,
                '<a href="$site.url$post.url" title="$post.title">$post.title</a>'
            )
        ));

        return $admin->form('[checkbox=default_subscribe]' . $tb->build($items) , $args);
    }

    public function processform() {
        UserOptions::i()->setvalue($this->iduser, 'subscribe', isset($_POST['default_subscribe']) ? 'enabled' : 'disabled');

        $subscribers = SubscriberItems::i();
        foreach ($_POST as $name => $value) {
            if (strbegin($name, 'checkbox-')) {
                $subscribers->remove((int)$value, $this->iduser);
            }
        }

        return $this->admin->h(Lang::admin()->unsubscribed);
    }

} //class