<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\plugins\tickets;

use litepubl\plugins\polls\Polls;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Vars;

class View extends \litepubl\post\View
{

    public function getTicketContent(): string
    {
        $lang = Lang::i('ticket');
        $args = new Args();
        foreach ([
            'state',
            'prio'
        ] as $prop) {
            $value = $this->$prop;
            $args->$prop = $lang->$value;
        }
        $args->reproduced = $this->reproduced ? $lang->yesword : $lang->noword;
        $args->assignto = $this->assigntoname;
        $args->author = $this->authorlink;

        $vars = new Vars();
        $vars->ticket = $this;
        $theme = $this->theme;
        $tml = file_get_contents(__DIR__ . '/resource/ticket.tml');
        return $theme->parseArg($tml, $args);
    }

    protected function getContentpage(int $page): string
    {
        $result = parent::getcontentpage($page);
        $result.= Polls::i()->getObjectPoll($this->id, 'post');
        return $result;
    }

    protected function getAssignToName()
    {
        return $this->getUserName($this->assignto, true);
    }
}
