<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\comments;
use litepubl\view\Theme;
use litepubl\view\Filter;
use litepubl\view\Args;
use litepubl\view\Vars;
use litepubl\view\Lang;
use litepubl\post\Post;
use litepubl\utils\Mailer
use litepubl\core\Array2prop;

class tpingbacks extends \litepubl\core\Items
{
    public $pid;

    public static function i($pid = 0) {
        $result = getinstance(__class__);
        $result->pid = $pid;
        return $result;
    }

    protected function create() {
        $this->dbversion = true;
        parent::create();
        $this->table = 'pingbacks';
        $this->basename = 'pingbacks';
    }


    public function add($url, $title) {
        $filter = Filter::i();
        $title = $filter->gettitle($title);
        $id = $this->doadd($url, $title);
        $this->added($id);
        $this->sendmail($id);
        return $id;
    }

    public function hold($id) {
        return $this->setstatus($id, false);
    }

    public function approve($id) {
        return $this->setstatus($id, true);
    }

    private function sendmail($id) {
        $item = $this->getitem($id);
        $args = new Args();
        $args->add($item);
        $args->id = $id;
        $status = dbversion ? $item['status'] : ($item['approved'] ? 'approved' : 'hold');
        $args->localstatus = Lang::get('commentstatus', $status);
        $args->adminurl = litepubl::$site->url . '/admin/comments/pingback/' . litepubl::$site->q . "id=$id&post={$item['post']}&action";
        $post = Post::i($item['post']);
        $args->posttitle = $post->title;
        $args->postlink = $post->link;

        Lang::usefile('mail');
        $lang = Lang::i('mailcomments');
        $theme = Theme::i();

        $subject = $theme->parsearg($lang->pingbacksubj, $args);
        $body = $theme->parsearg($lang->pingbackbody, $args);

        Mailer::sendmail(litepubl::$site->name, litepubl::$options->fromemail, 'admin', litepubl::$options->email, $subject, $body);

    }

    public function doadd($url, $title) {
        $item = array(
            'url' => $url,
            'title' => $title,
            'post' => $this->pid,
            'posted' => sqldate() ,
            'status' => 'hold',
            'ip' => preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'])
        );
        $id = $this->db->add($item);
        $item['id'] = $id;
        $this->items[$id] = $item;
        $this->updatecount($this->pid);
        return $id;
    }

    private function updatecount($idpost) {
        $count = $this->db->getcount("post = $idpost and status = 'approved'");
        $this->getdb('posts')->setvalue($idpost, 'pingbackscount', $count);
    }

    public function edit($id, $title, $url) {
        $this->db->updateassoc(compact('id', 'title', 'url'));
    }

    public function exists($url) {
        return $this->db->finditem('url =' . dbquote($url));
    }

    public function setstatus($id, $approve) {
        $status = $approve ? 'approved' : 'hold';
        $item = $this->getitem($id);
        if ($item['status'] == $status) return false;
        $db = $this->db;
        $db->setvalue($id, 'status', $status);
        $this->updatecount($item['post']);
    }

    public function postdeleted($idpost) {
        $this->db->delete("post = $idpost");
    }

    public function import($url, $title, $posted, $ip, $status) {
        $item = array(
            'url' => $url,
            'title' => $title,
            'post' => $this->pid,
            'posted' => sqldate($posted) ,
            'status' => $status,
            'ip' => $ip
        );
        $id = $this->db->add($item);
        $item['id'] = $id;
        $this->items[$id] = $item;
        $this->updatecount($this->pid);
        return $id;
    }
    public function getcontent() {
        $result = '';
        $items = $this->db->getitems("post = $this->pid and status = 'approved' order by posted");
        $pingback = new Array2prop();
$vars = new Vars();
$vars->pingback = $pingback;
        $lang = Lang::i('comment');
        $theme = Theme::i();
        $tml = $theme->templates['content.post.templatecomments.pingbacks.pingback'];
        foreach ($items as $item) {
            $pingback->array = $item;
            $result.= $theme->parse($tml);
        }
        return str_replace('$pingback', $result, $theme->parse($theme->templates['content.post.templatecomments.pingbacks']));
    }

} //class