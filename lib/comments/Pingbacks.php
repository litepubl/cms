<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\comments;

use litepubl\core\Event;
use litepubl\core\Str;
use litepubl\post\Post;
use litepubl\utils\Mailer;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Vars;

class Pingbacks extends \litepubl\core\Items
{
    public $pid;

    public static function i($pid = 0)
    {
        $result = static ::iGet(__class__);
        $result->pid = $pid;
        return $result;
    }

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->table = 'pingbacks';
        $this->basename = 'pingbacks';
    }

    public function add($url, $title)
    {
        $filter = Filter::i();
        $title = $filter->gettitle($title);
        $id = $this->doadd($url, $title);
        $this->added($id);
        $this->sendmail($id);
        return $id;
    }

    public function hold($id)
    {
        return $this->setstatus($id, false);
    }

    public function approve($id)
    {
        return $this->setstatus($id, true);
    }

    private function sendmail($id)
    {
        $item = $this->getitem($id);
        $args = new Args();
        $args->add($item);
        $args->id = $id;
        $status = $item['status'];
        $args->localstatus = Lang::get('commentstatus', $status);
        $args->adminurl = $this->getApp()->site->url . '/admin/comments/pingback/' . $this->getApp()->site->q . "id=$id&post={$item['post']}&action";
        $post = Post::i($item['post']);
        $args->posttitle = $post->title;
        $args->postlink = $post->link;

        Lang::usefile('mail');
        $lang = Lang::i('mailcomments');
        $theme = Theme::i();

        $subject = $theme->parseArg($lang->pingbacksubj, $args);
        $body = $theme->parseArg($lang->pingbackbody, $args);

        Mailer::sendmail($this->getApp()->site->name, $this->getApp()->options->fromemail, 'admin', $this->getApp()->options->email, $subject, $body);

    }

    public function doadd($url, $title)
    {
        $item = [
            'url' => $url,
            'title' => $title,
            'post' => $this->pid,
            'posted' => Str::sqlDate() ,
            'status' => 'hold',
            'ip' => preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'])
        ];
        $id = $this->db->add($item);
        $item['id'] = $id;
        $this->items[$id] = $item;
        $this->updatecount($this->pid);
        return $id;
    }

    private function updatecount($idpost)
    {
        $count = $this->db->getcount("post = $idpost and status = 'approved'");
        $this->getdb('posts')->setvalue($idpost, 'pingbackscount', $count);
    }

    public function edit($id, $title, $url)
    {
        $this->db->updateassoc(compact('id', 'title', 'url'));
    }

    public function exists($url)
    {
        return $this->db->finditem('url =' . Str::quote($url));
    }

    public function setStatus($id, $approve)
    {
        $status = $approve ? 'approved' : 'hold';
        $item = $this->getitem($id);
        if ($item['status'] == $status) {
            return false;
        }

        $db = $this->db;
        $db->setvalue($id, 'status', $status);
        $this->updatecount($item['post']);
    }

    public function postDeleted(Event $event)
    {
        $this->db->delete("post = $event->id");
    }

    public function import($url, $title, $posted, $ip, $status)
    {
        $item = [
            'url' => $url,
            'title' => $title,
            'post' => $this->pid,
            'posted' => Str::sqlDate($posted) ,
            'status' => $status,
            'ip' => $ip
        ];
        $id = $this->db->add($item);
        $item['id'] = $id;
        $this->items[$id] = $item;
        $this->updatecount($this->pid);
        return $id;
    }
    public function getContent()
    {
        $result = '';
        $items = $this->db->getitems("post = $this->pid and status = 'approved' order by posted");
        $pingback = new \ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
        $vars = new Vars();
        $vars->pingback = $pingback;
        $lang = Lang::i('comment');
        $theme = Theme::i();
        $tml = $theme->templates['content.post.templatecomments.pingbacks.pingback'];
        foreach ($items as $item) {
            $pingback->exchangeArray($item);
            $result.= $theme->parse($tml);
        }
        return str_replace('$pingback', $result, $theme->parse($theme->templates['content.post.templatecomments.pingbacks']));
    }
}
