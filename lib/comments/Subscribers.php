<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\comments;
use litepubl\core\Users;
use litepubl\core\UserOptions;
use litepubl\core\Cron;
use litepubl\post\Posts;
use litepubl\post\Post;
use litepubl\view\Theme;
use litepubl\view\Vars;
use litepubl\view\Lang;
use litepubl\utils\Mailer;

class Subscribers extends \litepubl\core\ItemsPosts
{
    public $blacklist;

    protected function create() {
        $this->dbversion = true;
        parent::create();
        $this->table = 'subscribers';
        $this->basename = 'subscribers';
        $this->data['fromemail'] = '';
        $this->data['enabled'] = true;
        $this->addmap('blacklist', array());
    }

    public function getstorage() {
        return litepubl::$storage;
    }

    public function update($pid, $uid, $subscribed) {
        if ($subscribed == $this->exists($pid, $uid)) return;
        $this->remove($pid, $uid);
        $user = Users::i()->getitem($uid);
        if (in_array($user['email'], $this->blacklist)) return;
        if ($subscribed) $this->add($pid, $uid);
    }

    public function setenabled($value) {
        if ($this->enabled != $value) {
            $this->data['enabled'] = $value;
            $this->save();
            $comments = Comments::i();
            if ($value) {
                Posts::i()->added = $this->postadded;

                $comments->lock();
                $comments->added = $this->sendmail;
                $comments->onapproved = $this->sendmail;
                $comments->unlock();
            } else {
                $comments->unbind($this);
                Posts::i()->delete_event_class('added', get_class($this));
            }
        }
    }

    public function postadded($idpost) {
        $post = Post::i($idpost);
        if ($post->author <= 1) {
return;
}

        $useroptions = UserOptions::i();
        if ('enabled' == $useroptions->getvalue($post->author, 'authorpost_subscribe')) {
            $this->add($idpost, $post->author);
        }
    }

    public function getlocklist() {
        return implode("\n", $this->blacklist);
    }

    public function setlocklist($s) {
        $this->setblacklist(explode("\n", strtolower(trim($s))));
    }

    public function setblacklist(array $a) {
        $a = array_unique($a);
        array_delete_value($a, '');
        $this->data['blacklist'] = $a;
        $this->save();

        $dblist = array();
        foreach ($a as $s) {
            if ($s == '') continue;
            $dblist[] = dbquote($s);
        }
        if (count($dblist) > 0) {
            $db = $this->db;
            $db->delete("item in (select id from $db->users where email in (" . implode(',', $dblist) . '))');
        }
    }

    public function sendmail($id) {
        if (!$this->enabled) return;
        $comments = Comments::i();
        if (!$comments->itemexists($id)) return;
        $item = $comments->getitem($id);
        if (($item['status'] != 'approved')) return;

        if (litepubl::$options->mailer == 'smtp') {
            Cron::i()->add('single', get_class($this) , 'cronsendmail', (int)$id);
        } else {
            $this->cronsendmail($id);
        }
    }

    public function cronsendmail($id) {
        $comments = Comments::i();
        try {
            $item = $comments->getitem($id);
        }
        catch(Exception $e) {
            return;
        }

        $subscribers = $this->getitems($item['post']);
        if (!$subscribers || (count($subscribers) == 0)) return;
        $comment = $comments->getcomment($id);
$vars = new Vars();
        $vars->comment = $comment;
        Lang::usefile('mail');
        $lang = Lang::i('mailcomments');
        $theme = Theme::i();
        $args = new targs();

        $subject = $theme->parsearg($lang->subscribesubj, $args);
        $body = $theme->parsearg($lang->subscribebody, $args);

        $body.= "\n";
        $adminurl = litepubl::$site->url . '/admin/subscribers/';

        $users = Users::i();
        $users->loaditems($subscribers);
        $list = array();
        foreach ($subscribers as $uid) {
            $user = $users->getitem($uid);
            if ($user['status'] == 'hold') continue;
            $email = $user['email'];
            if (empty($email)) continue;
            if ($email == $comment->email) continue;
            if (in_array($email, $this->blacklist)) continue;

            $admin = $adminurl;
            if ('comuser' == $user['status']) {
                $admin.= litepubl::$site->q . 'auth=';
                if (empty($user['cookie'])) {
                    $user['cookie'] = md5uniq();
                    $users->setvalue($user['id'], 'cookie', $user['cookie']);
                }
                $admin.= rawurlencode($user['cookie']);
            }

            $list[] = array(
                'fromname' => litepubl::$site->name,
                'fromemail' => $this->fromemail,
                'toname' => $user['name'],
                'toemail' => $email,
                'subject' => $subject,
                'body' => $body . $admin
            );
        }

        if (count($list)) {
Mailer::sendlist($list);
}
    }

}