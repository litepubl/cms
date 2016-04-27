<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Filter;

class tdownloaditem extends tpost {

    public static function i($id = 0) {
        return parent::iteminstance(__class__, $id);
    }

    public static function getChildtable() {
        return 'downloaditems';
    }

    public static function selectitems(array $items) {
        return static ::select_child_items('tickets', $items);
    }

    protected function create() {
        parent::create();
        $this->data['childdata'] = & $this->childdata;
        $this->childdata = array(
            'id' => 0,
            'type' => 'theme',
            'downloads' => 0,
            'downloadurl' => '',
            'authorurl' => '',
            'authorname' => '',
            'version' => '1.00',
            'votes' => 0,
            'poll' => 0
        );
    }

    public function getFactory() {
        return dlitemfactory::i();
    }

    protected function getAuthorname() {
        return $this->childdata['authorname'];
    }

    public function getParenttag() {
        return $this->type == 'theme' ?  $this->getApp()->options->downloaditem_themetag :  $this->getApp()->options->downloaditem_plugintag;
    }

    public function setTagnames($names) {
        $names = trim($names);
        if ($names == '') {
            $this->tags = array();
            return;
        }
        $parent = $this->getparenttag();
        $tags = ttags::i();
        $items = array();
        $list = explode(',', trim($names));
        foreach ($list as $title) {
            $title = Filter::escape($title);
            if ($title == '') {
 continue;
}


            $items[] = $tags->add($parent, $title);
        }

        $this->tags = $items;
    }

    public function get_excerpt() {
        return $this->getdownloadcontent() . $this->data['excerpt'];
    }

    protected function getContentpage($page) {
        $result = $this->theme->templates['custom']['siteform'];
        $result.= $this->getdownloadcontent();
        if ($this->poll > 0) {
            $polls = tpolls::i();
            $result.= $polls->gethtml($this->poll, true);
        }

        $result.= parent::getcontentpage($page);
        return $result;
    }

    public function getDownloadcontent() {
        Theme::$vars['lang'] = Lang::i('downloaditem');
        Theme::$vars['post'] = $this;
        $theme = $this->theme;
        return $theme->parse($theme->templates['custom']['downloaditem']);
    }

    public function getDownloadcount() {
        return sprintf(Lang::get('downloaditem', 'downloaded') , $this->downloads);
    }

    public function closepoll() {
        $polls = tpolls::i();
        $polls->db->setvalue($this->poll, 'status', 'closed');
    }

} //class
class dlitemfactory extends tpostfactory {

    public static function i() {
        return static::iGet(__class__);
    }

    public function getPosts() {
        return tdownloaditems::i();
    }

}