<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\admin\Link;
use litepubl\view\Lang;
use litepubl\view\Args;

class tadmintickets extends \litepubl\admin\Menu
 {

    public static function i($id = 0) {
        return parent::iteminstance(__class__, $id);
    }

    public function getContent() {
        $result = '';
        $tickets = ttickets::i();
        $perpage = 20;
        $where =  $this->getApp()->options->group == 'ticket' ? ' and author = ' .  $this->getApp()->options->user : '';

        switch ($this->name) {
            case 'opened':
                $where.= " and state = 'opened' ";
                break;


            case 'fixed':
                $where.= " and state = 'fixed' ";
                break;
        }

        $count = $tickets->getchildscount($where);
        $from = $this->getfrom($perpage, $count);

        if ($count > 0) {
            $items = $tickets->select("status <> 'deleted' $where", " order by posted desc limit $from, $perpage");
            if (!$items) $items = array();
        } else {
            $items = array();
        }

        $admintheme = $this->admintheme;
        $lang = Lang::admin('tickets');
        $lang->addsearch('ticket', 'tickets');
        $result.= $admintheme->h($admintheme->link('/admin/tickets/editor/', $lang->editortitle));
        $result.= $admintheme->getcount($from, $from + count($items) , $count);

        $tb = $this->newTable();
        $tb->setposts(array(
            array(
                'right',
                $lang->date,
                '$post.date'
            ) ,

            array(
                $lang->posttitle,
                '$post.bookmark'
            ) ,

            array(
                $lang->author,
                '$post.authorlink'
            ) ,

            array(
                $lang->status,
                '$poststatus'
            ) ,

            array(
                $lang->category,
                '$post.category'
            ) ,

            array(
                $lang->state,
                function (Table $t) {
                    return Lang::i()->__get(basetheme::$vars['post']->state);
                }
            ) ,

            array(
                $lang->edit,
                '<a href="' . Link::url('/admin/tickets/editor/', 'id') . '=$post.id">' . $lang->edit . '</a>'
            ) ,

        ));

        $table = $tb->build($items);

        //wrap form
        if ( $this->getApp()->options->group != 'ticket') {
            $args = new Args();
            $form = new adminform($args);
            $form->body = $table;
            $form->body.= $form->centergroup($this->html->getsubmit('delete', 'setdraft', 'publish', 'setfixed'));
            $form->submit = '';
            $result.= $form->get();
        } else {
            $result.= $table;
        }

        $theme = $this->theme;
        $result.= $theme->getpages($this->url,  $this->getApp()->context->request->page, ceil($count / $perpage));
        return $result;
    }

    public function processForm() {
        if ( $this->getApp()->options->group == 'ticket') {
 return '';
}


        $tickets = ttickets::i();
        $status = isset($_POST['publish']) ? 'published' : (isset($_POST['setdraft']) ? 'draft' : (isset($_POST['setfixed']) ? 'fixed' : 'delete'));
        foreach ($_POST as $key => $id) {
            if (!is_numeric($id)) {
 continue;
}


            $id = (int)$id;
            if ($status == 'delete') {
                $tickets->delete($id);
            } else {
                $ticket = tticket::i($id);
                if ($status == 'fixed') {
                    $ticket->set_state($status);
                } else {
                    $ticket->status = $status;
                }
                $tickets->edit($ticket);
            }
        }
    }

}