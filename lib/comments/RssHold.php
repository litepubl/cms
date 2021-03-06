<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\comments;

use litepubl\core\Context;
use litepubl\core\Event;
use litepubl\post\DomRss;
use litepubl\post\Rss;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Vars;

class RssHold extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
{
    public $url;

    protected function create()
    {
        parent::create();
        $this->basename = 'rss.holdcomments';
        $this->url = '/rss/holdcomments.xml';
        $this->data['idurl'] = 0;
        $this->data['count'] = 20;
        $this->data['template'] = '';
    }

    public function setKey($key)
    {
        if ($this->key != $key) {
            if ($key == '') {
                Manager::i()->unbind($self);
            } else {
                Manager::i()->changed = $this->commentschanged;
            }
            $this->data['key'] = $key;
            $this->save();
        }
    }

    public function commentschanged(Event $event)
    {
        $this->getApp()->cache->clearUrl($this->url);
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;

        if (!$this->getApp()->options->user) {
            return $response->forbidden();
        }

        $response->setXml();
        $rss = Rss::i();
        $rss->domrss = new DomRss;
        $this->dogetholdcomments($rss);
        $response->body.= $rss->domrss->GetStripedXML();
    }

    private function dogetholdcomments($rss)
    {
        $rss->domrss->CreateRoot($this->getApp()->site->url . $this->url, Lang::get('comment', 'onrecent') . ' ' . $this->getApp()->site->name);

        $db = $this->getApp()->db;
        $author = $this->getApp()->options->ingroup('moderator') ? '' : sprintf('%s.author = %d and ', $db->comments, $this->getApp()->options->user);
        $recent = $db->res2assoc(
            $db->query(
                "select $db->comments.*,
    $db->users.name as name, $db->users.email as email, $db->users.website as website,
    $db->posts.title as title, $db->posts.commentscount as commentscount,
    $db->urlmap.url as posturl
    from $db->comments, $db->users, $db->posts, $db->urlmap
    where $db->comments.status = 'hold' and $author
    $db->users.id = $db->comments.author and
    $db->posts.id = $db->comments.post and
    $db->urlmap.id = $db->posts.idurl and
    $db->posts.status = 'published'
    order by $db->comments.posted desc limit $this->count"
            )
        );

        $title = Lang::get('comment', 'onpost') . ' ';
        $comment = new \ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
        $vars = new Vars;
        $vars->comment = $comment;
        $theme = Theme::i();
        $tml = str_replace('$adminurl', '/admin/comments/' . $this->getApp()->site->q . 'id=$comment.id&action', $this->template);
        $lang = Lang::admin('comments');

        foreach ($recent as $item) {
            if ($item['website']) {
                $item['website'] = sprintf('<a href="%1$s">%1$s</a>', $item['website']);
            }
            $comment->exchangeArray($item);
            $comment->content = $theme->parse($tml);
            $rss->AddRSSComment($comment, $title . $comment->title);
        }
    }
}
