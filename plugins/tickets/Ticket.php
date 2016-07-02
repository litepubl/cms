<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\plugins\tickets;

use litepubl\core\Str;
use litepubl\view\Filter;
use litepubl\view\Lang;

class Ticket extends \litepubl\post\Post
{

    public static function getChildtable():string
    {
        return 'tickets';
    }

    protected function create()
    {
        parent::create();
        $this->childData = array(
            'state' => 'opened',
            'prio' => 'major',
            'assignto' => 0,
            'closed' => static::ZERODATE,
            'version' => $this->getApp()->options->version,
            'os' => '*',
            'reproduced' => false,
            'code' => ''
        );
    }

    public function getFactory()
    {
        return Factory::i();
    }

    protected function getCacheClosed(): int
    {
        return $this->childData['closed'] == static ::ZERODATE ? 0 : strtotime($this->childData['closed']);
    }

    public function setClosed(int $timestamp)
    {
        $this->childData['closed'] = Str::sqldate($timestamp);
        $this->cacheData['closed'] = $timestamp;
    }

    public function updateFiltered()
    {
        $result = $this->view->ticketContent;
        $filter = Filter::i();
        $filter->filterPost($this, $this->rawcontent);
        $result.= $this->filtered;

        if (!empty($this->childData['code'])) {
            $lang = Lang::i('ticket');
            $result.= sprintf('<h2>%s</h2>', $lang->code);
            $result.= highlight_string($this->code, true);
        }

        $this->filtered = $result;
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