<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\users;
use litepubl\core\Users as UserItems;
use litepubl\core\UserOptions;
use litepubl\pages\Users as UserPages;
use litepubl\admin\GetSchema;
use litepubl\view\Lang;
use litepubl\view\Args;

class Pages extends \litepubl\admin\Menu
{

    public function getIduser() {
        if ( $this->getApp()->options->ingroup('admin')) {
            $id = $this->idget();
        } else {
            $id =  $this->getApp()->options->user;
        }

        $users = UserItems::i();
        if ($users->itemexists($id) && ('approved' == $users->getvalue($id, 'status'))) {
 return $id;
}


        return false;
    }

    public function getContent() {
        $result = '';
        $users = UserItems::i();
$admin = $this->admintheme;
        $lang = Lang::admin('users');
        $args = new Args();

        if (!($id = $this->getiduser())) {
            if ( $this->getApp()->options->ingroup('admin')) {
 return $this->getUserList();
}


            return $this->notfound;
        }

        $pages = UserPages::i();
        $item = $users->getitem($id) + $pages->getitem($id);
        if (!isset($item['url'])) {
            $item['url'] = $item['idurl'] ?  $this->getApp()->router->getidurl($item['idurl']) : '';
        }
        $args->add($item);
        $args->formtitle = sprintf('<a href="$site.url%s">%s</a>', $item['url'], $item['name']);
        $tabs = $this->newTabs();
        $tabs->add($lang->title, '[text=name] [text=website]');
        if ('admin' ==  $this->getApp()->options->group) {
            $tabs->add($lang->schema, GetSchema::combo($item['idschema']));
            $tabs->add('SEO', '[text=url] [text=keywords] [text=description] [editor=head]');
        }
        $tabs->add($lang->text, '[editor=rawcontent]');

        $opt = UserOptions::i()->getitem($id);
        $args->subscribe = $opt['subscribe'] == 'enabled';
        $args->authorpost_subscribe = $opt['authorpost_subscribe'] == 'enabled';
        $tabs->add($lang->options, '
    [checkbox=subscribe]
    [checkbox=authorpost_subscribe]
    ');

        return $admin->form($tabs->get() , $args);
    }

    public function processForm() {
        extract($_POST, EXTR_SKIP);
        if (!($id = $this->getiduser())) {
 return;
}


        $item = array(
            'rawcontent' => trim($rawcontent) ,
            'content' => Filter::i()->filter($rawcontent)
        );

        if ('admin' ==  $this->getApp()->options->group) {
            $item['idschema'] = (int)$idschema;
            $item['url'] = $url;
            $item['head'] = $head;
            $item['keywords'] = $keywords;
            $item['description'] = $description;
        }

        $pages = UserPages::i();
        $pages->edit($id, $item);

        UserItems::i()->edit($id, array(
            'name' => $name,
            'website' => tcontentfilter::clean_website($website) ,
        ));

        $useroptions = UserOptions::i();
        $useroptions->setvalue($id, 'subscribe', isset($subscribe) ? 'enabled' : 'disabled');
        $useroptions->setvalue($id, 'authorpost_subscribe', isset($authorpost_subscribe) ? 'enabled' : 'disabled');
    }

    public function getUserList() {
        $users = UserItems::i();
        $pages = UserPages::i();
        $perpage = 20;
        $count = $pages->count;
        $from = $this->getfrom($perpage, $count);
        $p = $pages->thistable;
        $u = $users->thistable;
        $items = $users->res2items($users->db->query("
    select $u.*  from $u
    left join $p on $u.id = $p.id
    where not $p.id is null
    order by $u.id desc limit $from, $perpage"));

$admin = $this->admintheme;
        $lang = Lang::admin('users');
        $args = new Args();
        $args->adminurl = $this->adminurl;
        $result = $admin->h($lang->userstable);

        $tb = $this->newTable();
        $tb->setowner($users);
        $tb->setstruct(array(
            array(
                $lang->edit,
                sprintf('<a href="%s=$id">$name</a>', $this->adminurl)
            )
        ));

        $result.= $tb->build($items);

        $result.= $this->theme->getpages($this->url,  $this->getApp()->router->page, ceil($count / $perpage));
        return $result;
    }

}