<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\downloaditem;

use litepubl\admin\Link;
use litepubl\view\Args;
use litepubl\view\Lang;

class Admin extends \litepubl\admin\Menu
{

    public function getContent()
    {
        $result = '';
        $admintheme = $this->admintheme;
        $lang = Lang::admin('downloaditems');
        $lang->addsearch['downloaditem');

        $args = new Args();
        $args->adminurl = $this->adminurl;
        $editurl = Link::url('/admin/downloaditems/editor/', 'id');
        $args->editurl = $editurl;

        $downloaditems = Plugin::i();
        $perpage = 20;
        $where = $this->getApp()->options->group == 'downloaditem' ? ' and author = ' . $this->getApp()->options->user : '';

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

        $form = $this->newFform(new Args());
        $form->body = $admintheme->getcount($from, $from + count($items) , $count);
        $tb = $this->newTable();
        $tb->setPosts(array(
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
        $result.= $theme->getpages($this->url, $this->getApp()->context->request->page, ceil($count / $perpage));
        return $result;
    }

    public function processForm()
    {
        $downloaditems = tdownloaditems::i();
        if ($this->name == 'addurl') {
            $url = trim($_POST['url']);
            if ($url == '') {
                return '';
            }

            if ($downloaditem = taboutparser::parse($url)) {
                $id = $downloaditems->add($downloaditem);
                $this->getApp()->router->redir(Link::url('/admin/downloaditems/editor/', "id=$id"));
            }
            return '';
        }

        $status = isset($_POST['publish']) ? 'published' : (isset($_POST['setdraft']) ? 'draft' : 'delete');

        foreach ($_POST as $key => $id) {
            if (!is_numeric($id)) {
                continue;
            }

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

}

