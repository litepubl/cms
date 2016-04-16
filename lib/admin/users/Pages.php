<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\users;
use litepubl\core\Users;
use litepubl\core\UserOptions;
use litepubl\pages\Users as UserPages;
use litepubl\admin\GetSchema;
use litepubl\view\Lang;



class Pages extends \litepubl\admin\Menu
{

    public function getiduser() {
        if (litepubl::$options->ingroup('admin')) {
            $id = $this->idget();
        } else {
            $id = litepubl::$options->user;
        }

        $users = Users::i();
        if ($users->itemexists($id) && ('approved' == $users->getvalue($id, 'status'))) return $id;
        return false;
    }

    public function getcontent() {
        $result = '';
        $users = Users::i();
$admin = $this->admintheme;
        $lang = tlocal::admin('users');
        $args = new targs();

        if (!($id = $this->getiduser())) {
            if (litepubl::$options->ingroup('admin')) return $this->getUserList();
            return $this->notfound;
        }

        $pages = UserPages::i();
        $item = $users->getitem($id) + $pages->getitem($id);
        if (!isset($item['url'])) {
            $item['url'] = $item['idurl'] ? litepubl::$urlmap->getidurl($item['idurl']) : '';
        }
        $args->add($item);
        $args->formtitle = sprintf('<a href="$site.url%s">%s</a>', $item['url'], $item['name']);
        $tabs = $this->newTabs();
        $tabs->add($lang->title, '[text=name] [text=website]');
        if ('admin' == litepubl::$options->group) {
            $tabs->add($lang->schema, GetSchema::combo($item['idview']));
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

    public function processform() {
        extract($_POST, EXTR_SKIP);
        if (!($id = $this->getiduser())) return;
        $item = array(
            'rawcontent' => trim($rawcontent) ,
            'content' => Filter::i()->filter($rawcontent)
        );

        if ('admin' == litepubl::$options->group) {
            $item['idview'] = (int)$idview;
            $item['url'] = $url;
            $item['head'] = $head;
            $item['keywords'] = $keywords;
            $item['description'] = $description;
        }

        $pages = UserPages::i();
        $pages->edit($id, $item);

        Users::i()->edit($id, array(
            'name' => $name,
            'website' => tcontentfilter::clean_website($website) ,
        ));

        $useroptions = UserOptions::i();
        $useroptions->setvalue($id, 'subscribe', isset($subscribe) ? 'enabled' : 'disabled');
        $useroptions->setvalue($id, 'authorpost_subscribe', isset($authorpost_subscribe) ? 'enabled' : 'disabled');
    }

    public function getUserList() {
        $users = Users::i();
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
        $lang = tlocal::admin('users');
        $args = new targs();
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

        $result.= $this->theme->getpages($this->url, litepubl::$urlmap->page, ceil($count / $perpage));
        return $result;
    }

}