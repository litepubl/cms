<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\widget;

use litepubl\core\Event;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;

class Comments extends Widget
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.comments';
        $this->cache = 'include';
        $this->template = 'comments';
        $this->adminclass = '\litepubl\admin\widget\MaxCount';
        $this->data['maxcount'] = 7;
    }

    public function getDeftitle(): string
    {
        return Lang::get('default', 'recentcomments');
    }

    public function getContent(int $id, int $sidebar): string
    {
        $recent = $this->getrecent($this->maxcount);
        if (!count($recent)) {
            return '';
        }

        $result = '';
        $view = $this->getView();
        $tml = $view->getItem('comments', $sidebar);
        $url = $this->getApp()->site->url;
        $args = new Args();
        $args->onrecent = Lang::get('comment', 'onrecent');
        foreach ($recent as $item) {
            $args->add($item);
            $args->link = $url . $item['posturl'];
            $args->content = Filter::getexcerpt($item['content'], 120);
            $result.= $view->theme->parseArg($tml, $args);
        }
        return $view->getContent($result, 'comments', $sidebar);
    }

    public function changed(Event $event)
    {
        Cache::i()->removeWidget($this);
    }

    public function getRecent(int $count, string $status = 'approved'): array
    {
        $db = $this->getApp()->db;
        $result = $db->res2assoc(
            $db->query(
                "select $db->comments.*,
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
    order by $db->comments.posted desc limit $count"
            )
        );

        if ($this->getApp()->options->commentpages && !$this->getApp()->options->comments_invert_order) {
            foreach ($result as $i => $item) {
                $page = ceil($item['commentscount'] / $this->getApp()->options->commentsperpage);
                if ($page > 1) {
                    $result[$i]['posturl'] = rtrim($item['posturl'], '/') . "/page/$page/";
                }
            }
        }

        return $result;
    }
}
