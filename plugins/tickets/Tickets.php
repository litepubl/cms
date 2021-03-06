<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\tickets;

use litepubl\core\Event;
use litepubl\plugins\polls\Polls;
use litepubl\post\Post;
use litepubl\utils\Mailer;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Vars;

class Tickets extends \litepubl\post\Posts
{
    public $cats;

    protected function create()
    {
        parent::create();
        $this->childTable = 'tickets';
        $this->addMap('cats', []);
        $this->data['idcomauthor'] = 0;
    }

    public function newPost()
    {
        return Ticket::i();
    }

    public function createPoll(int $id): int
    {
        return polls::i()->add('like', $id, 'post');
    }

    public function filterCats(Post $post)
    {
        $cats = array_intersect($post->categories, $this->cats);
        if (!count($cats)) {
            $cats = [
                $this->cats[0]
            ];
        } elseif (count($cats) > 1) {
            $cats = [
                $cats[0]
            ];
        }

        $post->categories = $cats;
    }

    public function add(Post $post): int
    {
        $this->filterCats($post);
        $post->updateFiltered();

        $id = parent::add($post);
        $this->createPoll($id);
        $this->notify($post);
        return $id;
    }

    private function notify(Ticket $ticket)
    {
        $vars = new Vars;
        $vars->ticket = $ticket->getView();
        $args = new Args();
        $args->adminurl = $this->getApp()->site->url . '/admin/tickets/editor/' . $this->getApp()->site->q . 'id=' . $ticket->id;

        Lang::usefile('mail');
        $lang = Lang::i('mailticket');
        $lang->addSearch('ticket');
        $theme = Theme::i();

        $subject = $theme->parseArg($lang->subject, $args);
        $body = $theme->parseArg($lang->body, $args);

        Mailer::sendToAdmin($subject, $body);
    }

    public function edit(Post $post)
    {
        $this->filterCats($post);
        $post->updateFiltered();
        return parent::edit($post);
    }

    public function onExclude(Event $event)
    {
        if ($this->getApp()->options->group == 'ticket') {
            $admin = $event->target;
            $event->exclude = $admin->items[$event->id]['url'] == '/admin/posts/';
        }
    }
}
