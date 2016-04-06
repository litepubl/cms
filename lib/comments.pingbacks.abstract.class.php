<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

interface ipingbacks {
    public function doadd($url, $title);
    public function setstatus($id, $approve);
    public function getcontent();
    public function exists($url);
    public function import($url, $title, $posted, $ip, $status);
}

class tabstractpingbacks extends titems {
    public $pid;

    public function add($url, $title) {
        $filter = tcontentfilter::i();
        $title = $filter->gettitle($title);
        $id = $this->doadd($url, $title);
        $this->added($id);
        $this->sendmail($id);
        return $id;
    }

    public function hold($id) {
        return $this->setstatus($id, false);
    }

    public function approve($id) {
        return $this->setstatus($id, true);
    }

    private function sendmail($id) {
        $item = $this->getitem($id);
        $args = targs::i();
        $args->add($item);
        $args->id = $id;
        $status = dbversion ? $item['status'] : ($item['approved'] ? 'approved' : 'hold');
        $args->localstatus = tlocal::get('commentstatus', $status);
        $args->adminurl = litepubl::$site->url . '/admin/comments/pingback/' . litepubl::$site->q . "id=$id&post={$item['post']}&action";
        $post = tpost::i($item['post']);
        $args->posttitle = $post->title;
        $args->postlink = $post->link;

        tlocal::usefile('mail');
        $lang = tlocal::i('mailcomments');
        $theme = ttheme::i();

        $subject = $theme->parsearg($lang->pingbacksubj, $args);
        $body = $theme->parsearg($lang->pingbackbody, $args);

        tmailer::sendmail(litepubl::$site->name, litepubl::$options->fromemail, 'admin', litepubl::$options->email, $subject, $body);

    }

} //class