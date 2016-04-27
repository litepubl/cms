<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\view\Theme;

class ttickets extends tposts {
    public $cats;

    public static function i() {
        return static::iGet(__class__);
    }

    protected function create() {
        parent::create();
        $this->childtable = 'tickets';
        $this->addmap('cats', array());
        $this->data['idcomauthor'] = 0;
    }

    public function newpost() {
        return tticket::i();
    }

    public function createpoll($id) {
        return polls::i()->add('like', $id, 'post');
    }

    public function filtercats(tpost $post) {
        $cats = array_intersect($post->categories, $this->cats);
        if (count($cats) == 0) {
            $cats = array(
                $this->cats[0]
            );
        } elseif (count($cats) > 1) {
            $cats = array(
                $cats[0]
            );
        }

        $post->categories = $cats;
    }

    public function add(tpost $post) {
        $this->filtercats($post);
        $post->updatefiltered();

        $id = parent::add($post);
        $this->createpoll($id);
        $this->notify($post);
        return $id;
    }

    private function notify(tticket $ticket) {
        Theme::$vars['ticket'] = $ticket;
        $args = new Args();
        $args->adminurl =  $this->getApp()->site->url . '/admin/tickets/editor/' .  $this->getApp()->site->q . 'id=' . $ticket->id;

        Lang::usefile('mail');
        $lang = Lang::i('mailticket');
        $lang->addsearch('ticket');
        $theme = Theme::i();

        $subject = $theme->parsearg($lang->subject, $args);
        $body = $theme->parsearg($lang->body, $args);

        tmailer::sendtoadmin($subject, $body);
    }

    public function edit(tpost $post) {
        $this->filtercats($post);
        $post->updatefiltered();
        return parent::edit($post);
    }

    public function onexclude($id) {
        if ( $this->getApp()->options->group == 'ticket') {
            $admin = Menus::i();
            return $admin->items[$id]['url'] == '/admin/posts/';
        }
        return false;
    }

}