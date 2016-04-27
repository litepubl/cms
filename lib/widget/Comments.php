<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\widget;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Args;
use litepubl\view\Filter;

class Comments extends Widget
 {

    protected function create() {
        parent::create();
        $this->basename = 'widget.comments';
        $this->cache = 'include';
        $this->template = 'comments';
        $this->adminclass = '\litepubl\admin\widget\MaxCount';
        $this->data['maxcount'] = 7;
    }

    public function getDeftitle() {
        return Lang::get('default', 'recentcomments');
    }

    public function getContent($id, $sidebar) {
        $recent = $this->getrecent($this->maxcount);
        if (!count($recent)) {
return '';
}

        $result = '';
        $theme = Theme::i();
        $tml = $theme->getwidgetitem('comments', $sidebar);
        $url =  $this->getApp()->site->url;
        $args = new Args();
        $args->onrecent = Lang::get('comment', 'onrecent');
        foreach ($recent as $item) {
            $args->add($item);
            $args->link = $url . $item['posturl'];
            $args->content = Filter::getexcerpt($item['content'], 120);
            $result.= $theme->parsearg($tml, $args);
        }
        return $theme->getwidgetcontent($result, 'comments', $sidebar);
    }

    public function changed() {
        $this->expire();
    }

    public function getRecent($count, $status = 'approved') {
        $db =  $this->getApp()->db;
        $result = $db->res2assoc($db->query("select $db->comments.*,
    $db->users.name as name, $db->users.email as email, $db->users.website as url,
    $db->posts.title as title, $db->posts.commentscount as commentscount,
    $db->urlmap.url as posturl
    from $db->comments, $db->users, $db->posts, $db->urlmap
    where $db->comments.status = '$status' and
    $db->users.id = $db->comments.author and
    $db->posts.id = $db->comments.post and
    $db->urlmap.id = $db->posts.idurl and
    $db->posts.status = 'published' and
    $db->posts.idperm = 0
    order by $db->comments.posted desc limit $count"));

        if ( $this->getApp()->options->commentpages && ! $this->getApp()->options->comments_invert_order) {
            foreach ($result as $i => $item) {
                $page = ceil($item['commentscount'] /  $this->getApp()->options->commentsperpage);
                if ($page > 1) $result[$i]['posturl'] = rtrim($item['posturl'], '/') . "/page/$page/";
            }
        }
        return $result;
    }

}