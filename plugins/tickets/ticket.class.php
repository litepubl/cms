<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\core\Str;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\view\Theme;
use litepubl\view\Filter;

class tticket extends tpost {

    public static function i($id = 0) {
        return parent::iteminstance(__class__, $id);
    }

    public static function getChildtable() {
        return 'tickets';
    }

    public static function selectitems(array $items) {
        return static ::select_child_items('tickets', $items);
    }

    protected function create() {
        parent::create();
        $this->data['childdata'] = & $this->childdata;
        $this->childdata = array(
            'id' => 0,
            //'type' => 'bug',
            'state' => 'opened',
            'prio' => 'major',
            'assignto' => 0,
            'closed' => '',
            'version' =>  $this->getApp()->options->version,
            'os' => '*',
            'reproduced' => false,
            'code' => ''
        );
    }

    public function getFactory() {
        return ticketfactory::i();
    }

    public function beforedb() {
        if ($this->childdata['closed'] == '') $this->childdata['closed'] = Str::sqlDate();
    }

    public function afterdb() {
        $this->childdata['reproduced'] = $this->childdata['reproduced'] == '1';
    }

    protected function getClosed() {
        return strtotime($this->childdata['closed']);
    }

    protected function setClosed($value) {
        $this->childdata['closed'] = is_int($value) ? Str::sqlDate($value) : $value;
    }

    protected function getContentpage($page) {
        $result = parent::getcontentpage($page);
        $result.= polls::i()->getobjectpoll($this->id, 'post');
        return $result;
    }

    public function updatefiltered() {
        $result = $this->getticketcontent();
        $filter = Filter::i();
        $filter->filterpost($this, $this->rawcontent);
        $result.= $this->filtered;
        if (!empty($this->childdata['code'])) {
            $lang = Lang::i('ticket');
            $result.= sprintf('<h2>%s</h2>', $lang->code);
            $result.= highlight_string($this->code, true);
        }
        $this->filtered = $result;
    }

    public function getTicketcontent() {
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
        return $theme->parsearg($tml, $args);
    }

    protected function getAssigntoname() {
        return $this->getusername($this->assignto, true);
    }

    public static function getResource() {
        return  $this->getApp()->paths->plugins . 'tickets' . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    }

    public function getSchemalink() {
        return 'ticket';
    }

    public function set_state($state) {
        $old = $this->state;
        if ($state == $old) {
 return;
}


        $this->childdata['state'] = $state;
        if ($this->id == 0) {
 return;
}



        $lang = Lang::i('ticket');
        $content = sprintf($lang->statechanged, $lang->$old, $lang->$state);

        $this->comments->add($this->id, ttickets::i()->idcomauthor, $content, 'approved', '');
        //$this->commentscount = $this->comments->db->getcount("post = $this->id and status = 'approved'");
        
    }

} //class
class ticketfactory extends tpostfactory {

    public static function i() {
        return static::iGet(__class__);
    }

    public function getPosts() {
        return ttickets::i();
    }

}