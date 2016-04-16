<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;
use litepubl\admin\Link;

class tadmindownloaditems extends tadminmenu {

    public static function i($id = 0) {
        return parent::iteminstance(__class__, $id);
    }

    public function getcontent() {
        $result = '';
        $admintheme = $this->admintheme;
        $lang = tlocal::admin('downloaditems');
        $lang->ini['downloaditems'] = $lang->ini['downloaditem'] + $lang->ini['downloaditems'];

        $args = new targs();
        $args->adminurl = $this->adminurl;
        $editurl = Link::url('/admin/downloaditems/editor/', 'id');
        $args->editurl = $editurl;

        $downloaditems = tdownloaditems::i();
        $perpage = 20;
        $where = litepubl::$options->group == 'downloaditem' ? ' and author = ' . litepubl::$options->user : '';

        switch ($this->name) {
            case 'addurl':
                $args->formtitle = $lang->addurl;
                $args->url = $this->getparam('url', '');
                return $admintheme->form('[text=url]', $args);

            case 'theme':
                $where.= " and type = 'theme' ";
                break;


            case 'plugin':
                $where.= " and type = 'plugin' ";
                break;
        }

        $count = $downloaditems->getchildscount($where);
        $from = $this->getfrom($perpage, $count);
        if ($count > 0) {
            $items = $downloaditems->select("status <> 'deleted' $where", " order by posted desc limit $from, $perpage");
            if (!$items) $items = array();
        } else {
            $items = array();
        }

        $form = new adminform(new targs());
        $form->body = $admintheme->getcount($from, $from + count($items) , $count);
        $tb = new tablebuilder();
        $tb->setposts(array(
            array(
                'right',
                $lang->downloads,
                '$post.downloads'
            ) ,

            array(
                $lang->posttitle,
                '$post.bookmark'
            ) ,

            array(
                $lang->status,
                '$ticket_status.status'
            ) ,

            array(
                $lang->tags,
                '$post.tagnames'
            ) ,

            array(
                'center',
                $lang->edit,
                '<a href="' . $editurl . '=$post.id">' . $lang->edit . '</a>'
            ) ,
        ));

        $form->body.= $tb->build($items);
        $form->body.= $form->centergroup('[button=publish]
    [button=setdraft]
    [button=delete]');

        $form->submit = false;
        $result.= $form->get();

        $theme = $this->view->theme;
        $result.= $theme->getpages($this->url, litepubl::$urlmap->page, ceil($count / $perpage));
        return $result;
    }

    public function processform() {
        $downloaditems = tdownloaditems::i();
        if ($this->name == 'addurl') {
            $url = trim($_POST['url']);
            if ($url == '') return '';
            if ($downloaditem = taboutparser::parse($url)) {
                $id = $downloaditems->add($downloaditem);
                litepubl::$urlmap->redir(Link::url('/admin/downloaditems/editor/', "id=$id"));
            }
            return '';
        }

        $status = isset($_POST['publish']) ? 'published' : (isset($_POST['setdraft']) ? 'draft' : 'delete');

        foreach ($_POST as $key => $id) {
            if (!is_numeric($id)) continue;
            $id = (int)$id;
            if ($status == 'delete') {
                $downloaditems->delete($id);
            } else {
                $downloaditem = tdownloaditem::i($id);
                $downloaditem->status = $status;
                $downloaditems->edit($downloaditem);
            }
        }
    }

} //class