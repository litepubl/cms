<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class trssholdcomments extends tevents {
    public $url;

    protected function create() {
        parent::create();
        $this->basename = 'rss.holdcomments';
        $this->url = '/rss/holdcomments.xml';
        $this->data['idurl'] = 0;
        $this->data['count'] = 20;
        $this->data['template'] = '';
    }

    public function setkey($key) {
        if ($this->key != $key) {
            if ($key == '') {
                litepubl::$classes->commentmanager->unbind($self);
            } else {
                litepubl::$classes->commentmanager->changed = $this->commentschanged;
            }
            $this->data['key'] = $key;
            $this->save();
        }
    }

    public function commentschanged($idpost) {
        litepubl::$urlmap->setexpired($this->idurl);
    }

    public function request($arg) {
        if (!litepubl::$options->user) return 403;
        $result = '<?php litepubl::turlmap::sendxml(); ?>';
        $rss = trss::i();
        $rss->domrss = new tdomrss;
        $this->dogetholdcomments($rss);
        $result.= $rss->domrss->GetStripedXML();
        return $result;
    }

    private function dogetholdcomments($rss) {
        $rss->domrss->CreateRoot(litepubl::$site->url . $this->url, tlocal::get('comment', 'onrecent') . ' ' . litepubl::$site->name);

        $db = litepubl::$db;
        $author = litepubl::$options->ingroup('moderator') ? '' : sprintf('%s.author = %d and ', $db->comments, litepubl::$options->user);
        $recent = $db->res2assoc($db->query("select $db->comments.*,
    $db->users.name as name, $db->users.email as email, $db->users.website as website,
    $db->posts.title as title, $db->posts.commentscount as commentscount,
    $db->urlmap.url as posturl
    from $db->comments, $db->users, $db->posts, $db->urlmap
    where $db->comments.status = 'hold' and $author
    $db->users.id = $db->comments.author and
    $db->posts.id = $db->comments.post and
    $db->urlmap.id = $db->posts.idurl and
    $db->posts.status = 'published'
    order by $db->comments.posted desc limit $this->count"));

        $title = tlocal::get('comment', 'onpost') . ' ';
        $comment = new tarray2prop();
        ttheme::$vars['comment'] = $comment;
        $theme = ttheme::i();
        $tml = str_replace('$adminurl', '/admin/comments/' . litepubl::$site->q . 'id=$comment.id&action', $this->template);
        $lang = tlocal::admin('comments');

        foreach ($recent as $item) {
            if ($item['website']) $item['website'] = sprintf('<a href="%1$s">%1$s</a>', $item['website']);
            $comment->array = $item;
            $comment->content = $theme->parse($tml);
            $rss->AddRSSComment($comment, $title . $comment->title);
        }
    }

} //class