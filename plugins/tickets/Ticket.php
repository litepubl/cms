<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\tickets;

use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Theme;

class Ticket extends \litepubl\post\Post
{

    public static function getChildtable():string
    {
        return 'tickets';
    }

    protected function create()
    {
        parent::create();
        $this->childData = [
'type' => 'bug', 'state' => 'opened', 'prio' => 'major', 'assignto' => 0, 'closed' => static ::ZERODATE, 'version' => $this->getApp()->options->version, 'os' => '*', 'reproduced' => false, 'code' => ''];
    }

    public function getFactory()
    {
        return Factory::i();
    }

    protected function getCacheClosed()
    {
        return $this->childData['closed'] == static ::ZERODATA ? 0 : strtotime($this->childData['closed']);
    }

    public function setClosed($timestamp)
    {
        $this->childData['closed'] = Str::sqldate($timestamp);
        $this->cacheData['closed'] = $timestamp;
    }

    protected function getContentpage($page)
    {
        $result = parent::getcontentpage($page);
        $result.= polls::i()->getobjectpoll($this->id, 'post');
        return $result;
    }

    public function updatefiltered()
    {
        $result = $this->getticketcontent();
        $filter = Filter::i();
        $filter->filterpost($this, $this->rawcontent);
        $result.= $this->filtered;
        if (!empty($this->childData['code'])) {
            $lang = Lang::i('ticket');
            $result.= sprintf('<h2>%s</h2>', $lang->code);
            $result.= highlight_string($this->code, true);
        }
        $this->filtered = $result;
    }

    public function getTicketcontent()
    {
        $lang = Lang::i('ticket');
        $args = new Args();
        foreach (array(
            'state',
            'prio'
        ) as $prop) {
            $value = $this->$prop;
            $args->$prop = $lang->$value;
        }
        $args->reproduced = $this->reproduced ? $lang->yesword : $lang->noword;
        $args->assignto = $this->assigntoname;
        $args->author = $this->authorlink;

        Theme::$vars['ticket'] = $this;
        $theme = $this->theme;
        $tml = file_get_contents($this->resource . 'ticket.tml');
        return $theme->parseArg($tml, $args);
    }

    protected function getAssignToName()
    {
        return $this->getUserName($this->assignto, true);
    }

    public static function getResource()
    {
        return $this->getApp()->paths->plugins . 'tickets' . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    }

    public function getSchemaLink()
    {
        return 'ticket';
    }

    public function set_state($state)
    {
        $old = $this->state;
        if ($state == $old) {
            return;
        }

        $this->childData['state'] = $state;
        if ($this->id == 0) {
            return;
        }

        $lang = Lang::i('ticket');
        $content = sprintf($lang->statechanged, $lang->$old, $lang->$state);

        $this->comments->add($this->id, Tickets::i()->idcomauthor, $content, 'approved', '');
    }

}

