<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\plugins\tickets;

use litepubl\post\Post;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;

class Editor extends \litepubl\admin\posts\Editor
{
    private $newstatus;

    public function getTitle(): string
    {
        Lang::admin()->addSearch('tickets', 'ticket', 'editor');
        if ($this->idpost == 0) {
            return parent::gettitle();
        } else {
            return Lang::admin('tickets')->editor;
        }
    }

    public function canRequest()
    {
        if ($r = parent::canRequest()) {
            return $r;
        }

        $this->basename = 'tickets';
        if ($this->idpost > 0) {
            $ticket = Ticket::i($this->idpost);
            if (($this->getApp()->options->group == 'ticket') && ($this->getApp()->options->user != $ticket->author)) {
                return 403;
            }
        }
    }

    public function getTabsTemplate()
    {
        return strtr(
            $this->admintheme->templates['tabs'], [
            '$id' => 'tabs',
            '$tab' => '[tab=ticket] [ajaxtab=tags]',
            '$panel' => '[tabpanel=ticket] [tabpanel=tags]'
            ]
        );
    }

    public function getArgstab(Post $ticket, Args $args)
    {
        $args->ajax = $this->getAjaxLink($ticket->id);
        $args->fixed = $ticket->state == 'fixed';

        $lang = Lang::admin('tickets');
        $tickets = Tickets::i();
        $args->category = static ::getComboCategories($tickets->cats, count($ticket->categories) ? $ticket->categories[0] : (count($tickets->cats) ? $tickets->cats[0] : 0));

        $args->version = $ticket->version;
        $args->os = $ticket->os;

        $states = [];
        foreach ([
            'fixed',
            'opened',
            'wontfix',
            'invalid',
            'duplicate',
            'reassign'
        ] as $state) {
            $states[$state] = $lang->$state;
        }

        $args->state = $this->theme->comboItems($states, $ticket->state);

        $prio = [];
        foreach ([
            'trivial',
            'minor',
            'major',
            'critical',
            'blocker'
        ] as $p) {
            $prio[$p] = $lang->$p;
        }

        $args->prio = $this->theme->comboItems($prio, $ticket->prio);

        $tb = $this->newTable($this->admintheme);
        $tb->args = $args;
        $args->ticket = $tb->inputs(
            [
            'category' => 'combo',
            'state' => 'combo',
            'prio' => 'combo',
            'version' => 'text',
            'os' => 'text',
            ]
        );
    }

    public function getText($post = null)
    {
        $post = $this->getvarpost($post);
        $admintheme = $this->admintheme;
        $lang = Lang::admin('tickets');
        $tabs = $this->newtabs($admintheme);
        $tabs->add($lang->text, '[editor=raw]');
        $tabs->add($lang->codetext, '[editor=code]');

        $args = new Args();
        $args->raw = $post->rawcontent;
        $args->code = $post->code;

        return $admintheme->parseArg($tabs->get(), $args);
    }

    public function newPost()
    {
        return new Ticket();
    }

    public function canProcess()
    {
        if ($error = parent::canProcess()) {
            return $error;
        }

        // check spam
        $tickets = Tickets::i();
        $id = (int)$_POST['id'];
        if ($id == 0) {
            $this->newstatus = 'published';
            if ($this->getApp()->options->group == 'ticket') {
                $hold = $tickets->db->getcount('status = \'draft\' and author = ' . $this->getApp()->options->user);
                $approved = $tickets->db->getcount('status = \'published\' and author = ' . $this->getApp()->options->user);
                if ($approved < 3) {
                    if ($hold - $approved >= 2) {
                        return Lang::admin('tickets')->noapproved;
                    }

                    $this->newstatus = 'draft';
                }
            }
        }
    }

    public function processTab(Post $ticket)
    {
        extract($_POST, EXTR_SKIP);

        $ticket->title = $title;
        $ticket->prio = $prio;
        $ticket->set_state($state);
        $ticket->version = $version;
        $ticket->os = $os;
        $ticket->categories = [
            (int)$category
        ];

        if (isset($tags)) {
            $ticket->tagnames = $tags;
        }

        if ($ticket->author == 0) {
            $ticket->author = $this->getApp()->options->user;
        }

        if ($ticket->id == 0) {
            $ticket->status = $this->newstatus;
            $ticket->closed = time();
        }

        $ticket->content = Filter::quote(htmlspecialchars($raw));
        $ticket->code = $code;
    }
}
