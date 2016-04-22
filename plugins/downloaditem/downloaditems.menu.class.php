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

class tdownloaditemsmenu extends tmenu {

    public static function i($id = 0) {
        return parent::iteminstance(__class__, $id);
    }

    protected function create() {
        parent::create();
        $this->data['type'] = '';
    }

    public function getCont() {
        $result = '';
        $theme = Theme::i();
        if (( $this->getApp()->router->page == 1) && ($this->content != '')) {
            $result.= $theme->simple($theme->parse($this->rawcontent));
        }

        $perpage =  $this->getApp()->options->perpage;
        $downloaditems = tdownloaditems::i();
        $d =  $this->getApp()->db->prefix . $downloaditems->childtable;
        $p =  $this->getApp()->db->posts;
        $where = $this->type == '' ? '' : " and $d.type = '$this->type'";
        $count = $downloaditems->getchildscount($where);
        $from = ( $this->getApp()->router->page - 1) * $perpage;
        if ($from <= $count) {
            $items = $downloaditems->select("$p.status = 'published' $where", " order by $p.posted desc limit $from, $perpage");
            Theme::$vars['lang'] = Lang::i('downloaditem');
            $tml = $theme->templates['custom']['downloadexcerpt'];
            if (count($items) > 0) {
                $result.= $theme->templates['custom']['siteform'];
                foreach ($items as $id) {
                    Theme::$vars['post'] = tdownloaditem::i($id);
                    $result.= $theme->parse($tml);
                }
            }
        }
        $result.= $theme->getpages($this->url,  $this->getApp()->router->page, ceil($count / $perpage));
        return $result;
    }

} 