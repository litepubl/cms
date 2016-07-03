<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\admin\views;

use litepubl\admin\Menus;
use litepubl\admin\posts\Ajax;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\MainView;
use litepubl\view\Schemes as SchemaItems;

class Head extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $result = '';
        $schemes = SchemaItems::i();
        $admin = $this->admintheme;
        $lang = Lang::i('schemes');
        $args = new Args();

        $tabs = $this->newTabs();
        $args->heads = MainView::i()->heads;
        $tabs->add($lang->headstitle, '[editor=heads]');

        $args->adminheads = Menus::i()->heads;
        $tabs->add($lang->admin, '[editor=adminheads]');

        $ajax = Ajax::i();
        $args->ajaxvisual = $ajax->ajaxvisual;
        $args->visual = $ajax->visual;
        $args->show_file_perm = $this->getApp()->options->show_file_perm;
        $tabs->add($lang->posteditor, '[checkbox=show_file_perm] [checkbox=ajaxvisual] [text=visual]');

        $args->formtitle = $lang->headstitle;
        return $admin->form($tabs->get(), $args);
    }

    public function processForm()
    {
        $template = MainView::i();
        $template->heads = $_POST['heads'];
        $template->save();

        $adminmenus = Menus::i();
        $adminmenus->heads = $_POST['adminheads'];
        $adminmenus->save();

        $ajax = Ajax::i();
        $ajax->lock();
        $ajax->ajaxvisual = isset($_POST['ajaxvisual']);
        $ajax->visual = trim($_POST['visual']);
        $ajax->unlock();

        $this->getApp()->options->show_file_perm = isset($_POST['show_file_perm']);
    }
}
