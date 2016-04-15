<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\comments;
use litepubl\core\Users;
use litepubl\view\Args;
use litepubl\comments\Comments;
use litepubl\comments\Subscribers;
use litepubl\admin;

class Authors extends Menu 
{

    public function getcontent() {
        $result = '';
        $this->basename = 'authors';
        $users = Users::i();
        $lang = $this->lang;

        if ('delete' == $this->action) {
            $id = $this->idget();
            if (!$users->itemexists($id)) return $this->notfound();
            if (!$this->confirmed) {
return $this->confirmDelete($id, $lang->confirmdelete);
}

            if (!$this->deleteauthor($id)) return $this->notfount;
            $result.= $html->h4->deleted;
        }

        $args = new targs();
        $perpage = 20;
        $total = $users->db->getcount("status = 'comuser'");
        $from = $this->getfrom($perpage, $total);
        $res = $users->db->query("select * from $users->thistable where status = 'comuser' order by id desc limit $from, $perpage");
        $items = litepubl::$db->res2assoc($res);

        $result.= sprintf($html->h4->itemscount, $from, $from + count($items) , $total);
        $adminurl = $this->adminurl;
        $editurl = tadminhtml::getadminlink('/admin/users/', 'id');
        $tb = new tablebuilder();
        $tb->setstruct(array(
            array(
                $lang->author,
                '$name'
            ) ,

            array(
                'E-Mail',
                '$email'
            ) ,

            array(
                $lang->website,
                '$website'
            ) ,

            array(
                $lang->edit,
                "<a href='$editurl=\$id&action=edit'>$lang->edit</a>"
            ) ,

            array(
                $lang->delete,
                "<a href='$adminurl=\$id&action=delete'>$lang->delete</a>"
            )
        ));

        $result.= $tb->build($items);
        $result.= $this->theme->getpages($this->url, litepubl::$urlmap->page, ceil($total / $perpage));
        return $result;
    }

    private function deleteauthor($uid) {
        $users = Users::i();
        if (!$users->itemexists($uid)) return false;
        if ('comuser' != $users->getvalue($uid, 'status')) return false;
        $comments = Cmments::i();
        $comments->db->delete("author = $uid");
        $users->setvalue($uid, 'status', 'hold');
        return true;
    }

    private function getsubscribed($authorid) {
        $db = litepubl::$db;
        $authorid = (int)$authorid;
        $users = Users::i();
        if (!$users->itemexists($authorid)) return '';
        $html = $this->gethtml('moderator');
        $result = '';
        $res = $db->query("select $db->posts.id as id, $db->posts.title as title, $db->urlmap.url as url
    from $db->posts, $db->urlmap
    where $db->posts.id in (select DISTINCT $db->comments.post from $db->comments where author = $authorid)
    and $db->urlmap.id = $db->posts.idurl
    order by $db->posts.posted desc");
        $items = $db->res2assoc($res);

        $subscribers = Subscribers::i();
        $subscribed = $subscribers->getposts($authorid);
        $args = mew Args();
        foreach ($items as $item) {
            $args->add($item);
            $args->subscribed = in_array($item['id'], $subscribed);
            $result.= $html->subscribeitem($args);
        }

        return $html->fixquote($result);
    }

    public function processform() {
        return '';
        $result = '';
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
            $id = $this->idget();
            $subscribers = Subscribers::i();
            $subscribed = $subscribers->getposts($id);
            $checked = array();
            foreach ($_POST as $idpost => $value) {
                if (!is_numeric($idpost)) continue;
                $checked[] = $idpost;
            }
            $unsub = array_diff($subscribed, $checked);
            if (count($unsub)) {
                foreach ($unsub as $idpost) {
                    $subscribers->delete($idpost, $id);
                }
            }

            $result = $this->html->h2->authoredited;
        }
    }

} //class