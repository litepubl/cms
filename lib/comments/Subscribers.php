<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\comments;

use litepubl\core\Arr;
use litepubl\core\Cron;
use litepubl\core\Str;
use litepubl\core\UserOptions;
use litepubl\core\Users;
use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\utils\Mailer;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Vars;
use litepubl\core\Event;

class Subscribers extends \litepubl\core\ItemsPosts
{
    public $blacklist;

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->table = 'subscribers';
        $this->basename = 'subscribers';
        $this->data['fromemail'] = '';
        $this->data['enabled'] = true;
        $this->addmap('blacklist', array());
    }

    public function getStorage()
    {
        return $this->getApp()->storage;
    }

    public function update($pid, $uid, $subscribed)
    {
        if ($subscribed == $this->exists($pid, $uid)) {
            return;
        }

        $this->remove($pid, $uid);
        $user = Users::i()->getitem($uid);
        if (in_array($user['email'], $this->blacklist)) {
            return;
        }

        if ($subscribed) {
            $this->add($pid, $uid);
        }
    }

    public function setEnabled($value)
    {
        if ($this->enabled != $value) {
            $this->data['enabled'] = $value;
            $this->save();

            $comments = Comments::i();
            if ($value) {
                Posts::i()->added = $this->postAdded;

                $comments->lock();
                $comments->added = $this->commentAdded;
                $comments->onapproved = $this->commentAdded;
                $comments->unlock();
            } else {
                $comments->unbind($this);
                Posts::i()->detach('added', $this->postAdded);
            }
        }
    }

    public function postAdded(Event $event)
    {
        $post = Post::i($event->id);
        if ($post->author <= 1) {
            return;
        }

        $useroptions = UserOptions::i();
        if ('enabled' == $useroptions->getValue($post->author, 'authorpost_subscribe')) {
            $this->add($post->id, $post->author);
        }
    }

    public function getLocklist()
    {
        return implode("\n", $this->blacklist);
    }

    public function setLocklist($s)
    {
        $this->setblacklist(explode("\n", strtolower(trim($s))));
    }

    public function setBlacklist(array $a)
    {
        $a = array_unique($a);
        Arr::deleteValue($a, '');
        $this->data['blacklist'] = $a;
        $this->save();

        $dblist = array();
        foreach ($a as $s) {
            if ($s == '') {
                continue;
            }

            $dblist[] = Str::quote($s);
        }
        if (count($dblist) > 0) {
            $db = $this->db;
            $db->delete("item in (select id from $db->users where email in (" . implode(',', $dblist) . '))');
        }
    }

public function commentAdded(Event $event)
{
$this->sendMail($event->id);
}

    public function sendMail(int $id)
    {
        if (!$this->enabled) {
            return;
        }

        $comments = Comments::i();
        if (!$comments->itemExists($id)) {
            return;
        }

        $item = $comments->getitem($id);
        if (($item['status'] != 'approved')) {
            return;
        }

        if ($this->getApp()->options->mailer == 'smtp') {
            Cron::i()->add('single', get_class($this), 'cronsendmail', (int)$id);
        } else {
            $this->cronsendmail($id);
        }
    }

    public function cronsendmail($id)
    {
        $comments = Comments::i();
        try {
            $item = $comments->getitem($id);
        } catch (\Exception $e) {
            return;
        }

        $subscribers = $this->getitems($item['post']);
        if (!$subscribers || (count($subscribers) == 0)) {
            return;
        }

        $comment = $comments->getcomment($id);
        $vars = new Vars();
        $vars->comment = $comment;
        Lang::usefile('mail');
        $lang = Lang::i('mailcomments');
        $theme = Theme::i();
        $args = new Args();

        $subject = $theme->parseArg($lang->subscribesubj, $args);
        $body = $theme->parseArg($lang->subscribebody, $args);

        $body.= "\n";
        $adminurl = $this->getApp()->site->url . '/admin/subscribers/';

        $users = Users::i();
        $users->loaditems($subscribers);
        $list = array();
        foreach ($subscribers as $uid) {
            $user = $users->getitem($uid);
            if ($user['status'] == 'hold') {
                continue;
            }

            $email = $user['email'];
            if (empty($email)) {
                continue;
            }

            if ($email == $comment->email) {
                continue;
            }

            if (in_array($email, $this->blacklist)) {
                continue;
            }

            $admin = $adminurl;
            if ('comuser' == $user['status']) {
                $admin.= $this->getApp()->site->q . 'auth=';
                if (empty($user['cookie'])) {
                    $user['cookie'] = Str::md5Uniq();
                    $users->setvalue($user['id'], 'cookie', $user['cookie']);
                }
                $admin.= rawurlencode($user['cookie']);
            }

            $list[] = array(
                'fromname' => $this->getApp()->site->name,
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
