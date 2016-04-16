<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\posts;
use litepubl\post\Posts as PostItems;
use litepubl\post\Post;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\admin\AuthorRights;
use litepubl\admin\Link;

class Posts extends \litepubl\admin\Menu
{
    private $isauthor;

    public function canrequest() {
        $this->isauthor = false;
        if (!litepubl::$options->hasgroup('editor')) {
            $this->isauthor = litepubl::$options->hasgroup('author');
        }
    }

    public function getcontent() {
        if (isset($_GET['action']) && in_array($_GET['action'], array(
            'delete',
            'setdraft',
            'publish'
        ))) {
            return $this->doaction(PostItems::i() , $_GET['action']);
        }

        return $this->gettable(PostItems::i() , $where = "status <> 'deleted' ");
    }

    public function doaction($posts, $action) {
        $id = $this->idget();
        if (!$posts->itemexists($id)) return $this->notfound;
        $post = Post::i($id);
        if ($this->isauthor && ($r = AuthorRights::i()->changeposts($action))) {
return $r;
}

        if ($this->isauthor && (litepubl::$options->user != $post->author)) {
return $this->notfound;
}

        $admintheme = $this->admintheme;
        if (!$this->confirmed) {
            $args = new Args();
            $args->id = $id;
            $args->adminurl = $this->adminurl;
            $args->action = $action;
            $args->confirm = sprintf($this->lang->confirm, $this->lang->$action, $post->bookmark);
            return $admintheme->parsearg($admintheme->templates['confirmform'], $args);
        }

        switch ($_GET['action']) {
            case 'delete':
                $posts->delete($id);
                $result = $admintheme->h($lang->confirmeddelete);
                break;


            case 'setdraft':
                $post->status = 'draft';
                $posts->edit($post);
                $result = $admintheme->h($lang->confirmedsetdraft);
                break;


            case 'publish':
                $post->status = 'published';
                $posts->edit($post);
                $result = $admintheme->h($lang->confirmedpublish);
                break;
        }

        return $result;
    }

    public function gettable($posts, $where) {
        $perpage = 20;
        if ($this->isauthor) $where.= ' and author = ' . litepubl::$options->user;
        $count = $posts->db->getcount($where);
        $from = $this->getfrom($perpage, $count);
        $items = $posts->select($where, " order by posted desc limit $from, $perpage");
        if (!$items) $items = array();

        $admintheme = $this->admintheme;
        $lang = tlocal::admin();
        $form = $this->newForm();
        $form->body = $admintheme->getcount($from, $from + count($items) , $count);

        $tb = $this->newTable();
        $tb->setposts(array(
            array(
                'center',
                $lang->date,
                '$post.date'
            ) ,
            array(
                $lang->posttitle,
                '$post.bookmark'
            ) ,
            array(
                $lang->category,
                '$post.category'
            ) ,
            array(
                $lang->status,
                '$poststatus'
            ) ,
            array(
                $lang->edit,
                '<a href="' . Link::url('/admin/posts/editor/?id') . '=$post.id">' . $lang->edit . '</a>'
            ) ,
            array(
                $lang->delete,
                "<a class=\"confirm-delete-link\" href=\"$this->adminurl=\$post.id&action=delete\">$lang->delete</a>"
            ) ,
        ));

        $form->body .= $tb->build($items);
        $form->body .= $form->centergroup('[button=publish]
    [button=setdraft]
    [button=delete]');

        $form->submit = false;
        $result = $form->get();
        $result.= $this->theme->getpages('/admin/posts/', litepubl::$urlmap->page, ceil($count / $perpage));
        return $result;
    }

    public function processform() {
        $posts = PostItems::i();
        $posts->lock();
        $status = isset($_POST['publish']) ? 'published' : (isset($_POST['setdraft']) ? 'draft' : 'delete');
        if ($this->isauthor && ($r = AuthorRights::i()->changeposts($status))) {
return $r;
}
        $iduser = litepubl::$options->user;
        foreach ($_POST as $key => $id) {
            if (!is_numeric($id)) continue;
            $id = (int)$id;
            if ($status == 'delete') {
                if ($this->isauthor && ($iduser != $posts->db->getvalue('author'))) continue;
                $posts->delete($id);
            } else {
                $post = tpost::i($id);
                if ($this->isauthor && ($iduser != $post->author)) continue;
                $post->status = $status;
                $posts->edit($post);
            }
        }
        $posts->unlock();
    }

} //class