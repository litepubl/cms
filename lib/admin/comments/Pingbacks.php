<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\admin\comments;

use litepubl\admin\Table;
use litepubl\comments\Pingbacks as PingItems;
use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;

class Pingbacks extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $result = '';
        $pingbacks = PingItems::i();
        $lang = $this->lang;
        $admin = $this->admintheme;

        if ($action = $this->action) {
            $id = $this->idget();
            if (!$pingbacks->itemExists($id)) {
                return $this->notfound;
            }

            switch ($action) {
            case 'delete':
                $result.= $this->confirmDeleteItem($pingbacks);
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

    private function getPingList()
    {
        $result = '';
        $pingbacks = PingItems::i();
        $perpage = 20;
        $total = $pingbacks->getcount();
        $from = $this->getfrom($perpage, $total);
        $db = $pingbacks->db;
        $t = $pingbacks->thistable;
        $items = $db->res2assoc(
            $db->query(
                "select $t.*, $db->posts.title as posttitle, $db->urlmap.url as posturl
    from $t, $db->posts, $db->urlmap
    where $t.status <> 'deleted' and $db->posts.id = $t.post and $db->urlmap.id = $db->posts.idurl
    order by $t.posted desc limit $from, $perpage"
            )
        );

        $admin = $this->admintheme;
        $lang = Lang::i();
        $args = new Args();
        $form = $this->newForm($args);
        $form->body = $admin->getcount($from, $from + count($items), $total);
        $tb = $this->newTable();
        $tb->setStruct(
            [
            $tb->checkbox('id') ,
            [
                $lang->date,
                function (Table $t) {
                
                    return $t->date($t->item['posted']);
                }
            ] ,
            [
                $lang->status,
                function (Table $t) {
                
                    return Lang::get('commentstatus', $t->item['status']);
                }
            ] ,
            [
                $lang->title,
                '$title'
            ] ,
            [
                $lang->url,
                '<a href="$url">$url</a>'
            ] ,
            [
                'IP',
                '$ip'
            ] ,
            [
                $lang->post,
                '<a href="$posturl">$posttitle</a>'
            ] ,
            [
                'center',
                $lang->edit,
                "<a href='$this->adminurl=\$id&action=edit'>$lang->edit</a>"
            ] ,
            ]
        );

        $form->items.= $tb->build($items);

        $form->body.= $form->centergroup($form->getButtons('approve', 'hold', 'delete'));
        $form->submit = false;
        $result = $form->get();

        $theme = Theme::i();
        $result.= $theme->getpages($this->url, $this->getApp()->context->request->page, ceil($total / $perpage));
        return $result;
    }

    private function editPingback($id)
    {
        $pingbacks = PingItems::i();
        $args = new Args();
        $args->add($pingbacks->getitem($id));
        $args->formtitle = Lang::i()->edit;
        return $this->admintheme->form(
            '
    [text=title]
    [text=url]
    ', $args
        );
    }

    public function processForm()
    {
        $pingbacks = PingItems::i();
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
            extract($_POST, EXTR_SKIP);
            $pingbacks->edit($this->idget(), $title, $url);
        } else {
            $status = isset($_POST['approve']) ? 'approve' : (isset($_POST['hold']) ? 'hold' : 'delete');
            foreach ($_POST as $k => $id) {
                if (!Str::begin($k, 'id-') || !is_numeric($id)) {
                    continue;
                }

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
