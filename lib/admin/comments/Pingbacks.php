<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\comments;
use litepubl\comments\Pingbacks as PingItems;
use litepubl\admin\Table;

class Pingbacks extends \litepubl\admin\Menu
{

    public function getcontent() {
        $result = '';
        $pingbacks = PingItems::i();
        $lang = $this->lang;
$admin = $this->admintheme;

        if ($action = $this->action) {
            $id = $this->idget();
            if (!$pingbacks->itemexists($id)) return $this->notfound;
            switch ($action) {
                case 'delete':
$result .= $this->confirmDeleteItem($pingbacks);
break;

                case 'hold':
                    $pingbacks->setstatus($id, false);
                    $result.= $admin->success($lang->successmoderated);
                    break;


                case 'approve':
                    $pingbacks->setstatus($id, true);
                    $result.= $admin->success($lang->successmoderated);
                    break;


                case 'edit':
                    $result.= $this->editPingback($id);
                    break;
                }
        }
        $result.= $this->getPingList();
        return $result;
    }

    private function getPingList() {
        $result = '';
        $pingbacks = PingItems::i();
        $perpage = 20;
        $total = $pingbacks->getcount();
        $from = $this->getfrom($perpage, $total);
        $db = $pingbacks->db;
        $t = $pingbacks->thistable;
        $items = $db->res2assoc($db->query("select $t.*, $db->posts.title as posttitle, $db->urlmap.url as posturl
    from $t, $db->posts, $db->urlmap
    where $t.status <> 'deleted' and $db->posts.id = $t.post and $db->urlmap.id = $db->posts.idurl
    order by $t.posted desc limit $from, $perpage"));

        $admin = $this->admintheme;
        $lang = tlocal::i();
        $args = new targs();
        $form = $this->newForm($args);
        $form->items = $admin->getcount($from, $from + count($items) , $total);
        $tb = $this->newTable();
        $tb->setstruct(array(
            $tb->checkbox('id') ,
            array(
                $lang->date,
function (Table $t) {
return $t->date($t->item['posted']);
}) ,
            array(
                $lang->status,
function(Table $t) {
                return tlocal::get('commentstatus', $t->item['status']);
}) ,
            array(
                $lang->title,
                '$title'
            ) ,
            array(
                $lang->url,
                '<a href="$url">$url</a>'
            ) ,
            array(
                'IP',
                '$ip'
            ) ,
            array(
                $lang->post,
                '<a href="$posturl">$posttitle</a>'
            ) ,
            array(
                'center',
                $lang->edit,
                "<a href='$this->adminurl=\$id&action=edit'>$lang->edit</a>"
            ) ,
        ));

        $form->items.= $tb->build($items);

        $form->body .= $form->centergroup($form->getButtons('approve', 'hold', 'delete'));
        $form->submit = false;
        $result = $form->get();

        $theme = ttheme::i();
        $result.= $theme->getpages($this->url, litepubl::$urlmap->page, ceil($total / $perpage));
        return $result;
    }

    private function editPingback($id) {
        $pingbacks = PingItems::i();
        $args = targs::i();
        $args->add($pingbacks->getitem($id));
        $args->formtitle = tlocal::i()->edit;
        return $this->admintheme->form('
    [text=title]
    [text=url]
    ', $args);
    }

    public function processform() {
        $pingbacks = PingItems::i();
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
            extract($_POST, EXTR_SKIP);
            $pingbacks->edit($this->idget() , $title, $url);
        } else {
            $status = isset($_POST['approve']) ? 'approve' : (isset($_POST['hold']) ? 'hold' : 'delete');
            foreach ($_POST as $k => $id) {
                if (!strbegin($k, 'id-') || !is_numeric($id)) continue;
                $id = (int)$id;
                if ($status == 'delete') {
                    $pingbacks->delete($id);
                } else {
                    $pingbacks->setstatus($id, $status == 'approve');
                }
            }
        }

        return $this->admintheme->success($this->lang->successmoderated);
    }

}