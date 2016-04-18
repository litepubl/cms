<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\comments;
use litepubl\comments\Comments;
use litepubl\comments\Comment;
use litepubl\comments\Manager;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Filter;

class Moderator extends \litepubl\admin\Menu
{
    private $moder;
    private $iduser;

    public function canrequest() {
        $this->moder = litepubl::$options->ingroup('moderator');
        $this->iduser = $this->moder ? (isset($_GET['iduser']) ? (int)$_GET['iduser'] : 0) : litepubl::$options->user;
    }

    public function can($id, $action) {
        if ($this->moder) {
return true;
}

        if (litepubl::$options->user != Comments::i()->getvalue($id, 'author')) {
return false;
}

        $cm = Manager::i();
        switch ($action) {
            case 'edit':
                return $cm->canedit;

            case 'delete':
                return $cm->candelete;
        }
        return false;
    }

    public function getcontent() {
        $result = '';
        $comments = Comments::i();
        $cm = Manager::i();
        $lang = $this->lang;
$admin = $this->admintheme;

        if ($action = $this->action) {
            $id = $this->idget();
            if (!$comments->itemexists($id)) {
                return $this->notfound;
            }

            switch ($action) {
                case 'delete':
                    if (!$this->can($id, 'delete')) {
                        return $admin->geterr($lang->forbidden);
                    }

                    if (!$this->confirmed) {
                        return $this->confirmDelete($id);
                    }

                    $comments->delete($id);
                    $result.= $admin->success($lang->successmoderated);
                    break;


                case 'hold':
                    if (!$this->moder) {
                        return $admin->geterr($lang->forbidden);
                    }

                    $comments->setstatus($id, 'hold');
                    $result.= $this->moderated($id);
                    break;


                case 'approve':
                    if (!$this->moder) {
                        return $admin->geterr($lang->forbidden);
                    }

                    $comments->setstatus($id, 'approved');
                    $result.= $this->moderated($id);
                    break;


                case 'edit':
                    if (!$this->can($id, 'edit')) {
                        return $admin->geterr($lang->forbidden);
                    }

                    $result.= $this->editcomment($id);
                    break;


                case 'reply':
                    if (!$this->can($id, 'edit')) {
                        return $admin->geterr($lang->forbidden);
                    }

                    $result.= $this->reply($id);
                    break;
            }
        }

        $result.= $this->get_table($this->name);
        return $result;
    }

    public function getinfo($comment) {
$admin = $this->admintheme;
        $lang = tlocal::admin();
        $tb = $this->newTable();
        $result = $tb->props(array(
            'commentonpost' => "<a href=\"$comment->url\">$comment->posttitle</a>",
            'author' => $comment->name,
            'E-Mail' => $comment->email,
            'IP' => $comment->ip,
            'website' => $comment->website ? "<a href=\"$comment->website\">$comment->website</a>" : '',
            'status' => $comment->localstatus,
        ));

        $result.= $admin->help($lang->content);
$result .= $admin->help($comment->content);
        $adminurl = $this->adminurl . "=$comment->id&action";
        $result.= $admin->help("
    $lang->cando:
    <a href='$adminurl=reply'>$lang->reply</a>,
    <a href='$adminurl=approve'>$lang->approve</a>,
    <a class'confirm-delete-link' href='$adminurl=delete'>$lang->delete</a>,
    <a href='$adminurl=hold'>$lang->hold</a>.
");

        return $result;
    }

    private function editComment($id) {
        $comment = new Comment($id);
        $args = new Args();
        $args->content = $comment->rawcontent;
        $args->formtitle = tlocal::i()->editform;
        $result = $this->getinfo($comment);
        $result.= $this->admin->form('[editor=content]', $args);
        return $result;
    }

    private function reply($id) {
        $comment = new Comment($id);
        $args = new targs();
        $args->pid = $comment->post;
        $args->formtitle = tlocal::i()->replyform;
        $result = $this->getinfo($comment);
        $args->content = '';
        $result.= $this->admintheme->form('
    [editor=content]
    [hidden=pid]
    ', $args);
        return $result;
    }

    //callback for table builder
    public function get_excerpt(tablebuilder $tb, Comment $comment) {
        $comment->id = $tb->id;
        $args = $tb->args;
        $args->id = $tb->id;
        $args->onhold = $comment->status == 'hold';
        $args->email = $comment->email == '' ? '' : "<a href='mailto:$comment->email'>$comment->email</a>";
        $args->website = $comment->website == '' ? '' : "<a href='$comment->website'>$comment->website</a>";
        return $this->admintheme->quote(Filter::getexcerpt($comment->content, 120));
    }

    protected function get_table($kind) {
        $comments = tcomments::i(0);
        $perpage = 20;
        // get total count
        $status = $kind == 'hold' ? 'hold' : 'approved';
        $where = "$comments->thistable.status = '$status'";
        if ($this->iduser) $where.= " and $comments->thistable.author = $this->iduser";
        $total = $comments->db->getcount($where);
        $from = $this->getfrom($perpage, $total);
        $list = $comments->select($where, "order by $comments->thistable.posted desc limit $from, $perpage");

$admin = $this->admintheme;
        $lang = tlocal::admin('comments');
        $form = new adminform(new targs());
        $form->title = sprintf($lang->itemscount, $from, $from + count($list) , $total);

        $comment = new Comment(0);
        basetheme::$vars['comment'] = $comment;

        $tablebuilder = new tablebuilder();
        $tablebuilder->addcallback('$excerpt', array(
            $this,
            'get_excerpt'
        ) , $comment);
        $tablebuilder->args->adminurl = $this->adminurl;

        $tablebuilder->setstruct(array(
            $tablebuilder->checkbox('id') ,

            array(
                $lang->date,
                '$comment.date',
            ) ,

            array(
                $lang->status,
                '$comment.localstatus',
            ) ,

            array(
                $lang->author,
                '<a href="$site.url/admin/users/{$site.q}id=$comment.author&action=edit">$comment.name</a>',
            ) ,

            array(
                'E-Mail',
                '$email',
            ) ,

            array(
                $lang->website,
                '$website',
            ) ,

            array(
                $lang->post,
                '<a href="$comment.url">$comment.posttitle</a>',
            ) ,

            array(
                $lang->content,
                '$excerpt',
            ) ,

            array(
                'IP',
                '$comment.ip',
            ) ,

            array(
                $lang->reply,
                '<a href="$adminurl=$comment.id&action=reply">$lang.reply</a>',
            ) ,

            array(
                $lang->edit,
                '<a href="$adminurl=$comment.id&action=edit">$lang.edit</a>',
            ) ,
        ));

        $form->before = $this->view->admintheme->templates['tablecols'];
        $form->body = $tablebuilder->build($list);
        $form->body .= $form->centergroup($formtml->getButtons('approve', 'hold', 'delete'));
        $form->submit = '';
        $result = $form->get();

        $theme = $this->view->theme;
        $result.= $theme->getpages($this->url, litepubl::$urlmap->page, ceil($total / $perpage) , ($this->iduser ? "iduser=$this->iduser" : ''));

        return $result;
    }

    private function moderated($id) {
        $result = $this->admintheme->success($this->lang->successmoderated);
        $result.= $this->getinfo(new Comment($id));
        return $result;
    }

    public function confirmDelete($id, $mesg = false) {
        $result = parent::confirmDelete($id);
        $result.= $this->getinfo(new Comment($id));
        return $result;
    }

    public function processform() {
        $result = '';
        $comments = tcomments::i();
        if (isset($_REQUEST['action'])) {
            switch ($_REQUEST['action']) {
                case 'reply':
                    if (!$this->moder) {
                        return $this->admintheme->geterr($this->lang->forbidden);
                    }

                    $item = $comments->getitem($this->idget());
                    $post = tpost::i((int)$item['post']);
                    $this->manager->reply($this->idget() , $_POST['content']);
                    return litepubl::$urlmap->redir($post->lastcommenturl);
                    break;


                case 'edit':
                    if (!$this->can($id, 'edit')) {
                        return $this->admintheme->geterr($this->lang->forbidden);
                    }

                    $comments->edit($this->idget() , $_POST['content']);
                    return $this->admintheme->success($this->lang->successmoderated);
                    break;
            }
        }

        $status = isset($_POST['approve']) ? 'approved' : (isset($_POST['hold']) ? 'hold' : 'delete');
        foreach ($_POST as $key => $id) {
            if (!is_numeric($id)) continue;
            $id = (int)$id;
            if (!$id) continue;

            if ($status == 'delete') {
                if ($this->can($id, 'delete')) $comments->delete($id);
            } else {
                if ($this->moder) $comments->setstatus($id, $status);
            }
        }

                    return $this->admintheme->success($this->lang->successmoderated);
    }

    public static function refilter() {
        $db = litepubl::$db;
        $filter = tcontentfilter::i();
        $from = 0;
        while ($a = $db->res2assoc($db->query("select id, rawcontent from $db->rawcomments where id > $from limit 500"))) {
            $db->table = 'comments';
            foreach ($a as $item) {
                $s = $filter->filtercomment($item['rawcontent']);
                $db->setvalue($item['id'], 'content', $s);
                $from = max($from, $item['id']);
            }
            unset($a);
        }
    }

} //class